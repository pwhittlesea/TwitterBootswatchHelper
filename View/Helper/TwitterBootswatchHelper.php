<?php
App::uses('AppHelper', 'Helper');
App::uses('HttpSocket', 'Network/Http');
App::uses('File', 'Utility');
App::uses('Folder', 'Utility');

/**
 * Helper for working with Bootswatch API
 */
class TwitterBootswatchHelper extends AppHelper {

    public $helpers = array('Html');

    private $_id = 'TwitterBootswatch';

    private $_bw = 'http://api.bootswatch.com/';

    private $_themes = array();

    /*
     * getThemes
     * Retrieve all of the themes
     *
     * @return array themes
     */
    public function getThemes() {
        if ($this->_themes == null || empty($this->_themes)) {
            $this->_fetch_local_themes();
        }
        if (empty($this->_themes) && $this->_fetch_from_api()) {
            $this->_cache_locally();
            $this->_fetch_local_themes();
        }
        $themes = array();

        foreach ($this->_themes as $a => $_t) {
            $themes[$a] = array(
                'name' => $_t['name'],
                'description' => (isset($_t['description'])) ? $_t['description'] : '',
                'css' => (isset($_t['css-local'])) ? $_t['css-local'] : $_t['css-min'],
                'thumbnail' => (isset($_t['thumbnail-local'])) ? $_t['thumbnail-local'] : $_t['thumbnail']
            );
        }
        return $themes;
    }

    /*
     * cssForTheme
     * Return plugin link for css
     */
    public function cssForTheme($name = null) {
        if ($name == null)
            return null;

        return $this->Html->css($this->Html->url("/".$this->_id."/css/$name.css", true));
    }

    /*
     * _fetch_from_api
     * Retrieve all of the themes from Bootswatch
     */
    private function _fetch_from_api() {
        $HttpSocket = new HttpSocket();
        // query api
        $results = $HttpSocket->get($this->_bw);
        $_t = json_decode($results, true);
        if (isset($_t['themes'])) {
            foreach ($_t['themes'] as $a => $theme) {
                $_t[$this->_sort_name($theme['name'])] = $theme;
            }
            unset($_t['themes']);
            $this->_themes = $_t;
            return true;
        } else {
            $this->_themes = array();
            return false;
        }
    }

    /*
     * _cache_locally
     * Cache all of the themes from Bootswatch locally
     */
    private function _cache_locally() {
        $HttpSocket = new HttpSocket();
        $cssFolder = new Folder(App::pluginPath($this->_id).'webroot/css');
        $imgFolder = new Folder(App::pluginPath($this->_id).'webroot/img');

        foreach ($this->_themes as $a => $theme) {
            $file = new File($cssFolder->path."/$a.css");

            if (!$file->exists()) {
                if ($file->create()) {
                    $file->write($HttpSocket->get($theme['css-min']), 'w');
                }
            }

            $file = new File($imgFolder->path."/$a.png");

            if (!$file->exists()) {
                if ($file->create()) {
                    $file->write($HttpSocket->get($theme['thumbnail']), 'w');
                }
            }
        }
    }

    /*
     * _fetch_local_themes
     * Fetch all of the themes stored locally
     */
    private function _fetch_local_themes() {
        $cssFolder = new Folder(App::pluginPath($this->_id).'webroot/css');
        $imgFolder = new Folder(App::pluginPath($this->_id).'webroot/img');

        foreach ($cssFolder->find('^.*\.css$') as $_css) {
            preg_match("#^(.*)\.css$#", $_css, $_css_grep);
            if(isset($this->_themes[$_css_grep[1]]))
                $this->_themes[$_css_grep[1]]['css-local'] = $this->Html->url("/".$this->_id."/css/$_css", true);
            else
                $this->_themes[$_css_grep[1]] = array('name' => ucfirst($_css_grep[1]), 'css-local' => $this->Html->url("/".$this->_id."/css/$_css", true));
        }
        foreach ($imgFolder->find('^.*\.png$') as $_img) {
            preg_match("#^(.*)\.png$#", $_img, $_img_grep);
            if(isset($this->_themes[$_img_grep[1]]))
                $this->_themes[$_img_grep[1]]['thumbnail-local'] = $this->Html->url("/".$this->_id."/img/$_img", true);
        }
    }

    private function _sort_name($string) {
        return strtolower(str_replace(' ', '_', $string));
    }
}