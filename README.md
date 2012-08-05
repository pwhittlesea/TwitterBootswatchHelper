TwitterBootswatchHelper
=======================

An automatic api.bootswatch.com fetch mechanism for CakePHP 2.0 apps

Installation
-----

Firstly, check out TwitterBootswatchHelper into your 'APP/app/Plugin' directory and update the submodules:
```bash
$ cd app/Plugin/
$ git clone git@github.com/pwhittlesea/TwitterBootswatchHelper TwitterBootswatch
```
Secondly, add TwitterBootswatchHelper to your APP/app/Config/bootstrap.php config file:
```php
<?php
...
CakePlugin::load('TwitterBootswatch');
...
```
Thirdly, import the Plugin in your controller:
```php
<?php
/**
 * DemoController
 * For a demo
 *
 */
class DemoController extends AppController {
  public $helpers = array('TwitterBootswatch');
  ...
}
```
Finally, you can now use the TwitterBootswatch Helper in your views:
```php
<h3>Select your theme</h3>
<?php
  $themes = array();
  foreach ($this->TwitterBootswatch->getThemes() as $a => $theme) {
    $themes[$a] = $theme['name'].' '.$theme['description'];
  }
?>
<?= $this->Form->radio("theme", $themes) ?>
```