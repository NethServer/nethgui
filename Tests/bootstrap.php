<?php
/**
 * PHPUnit bootstrap file
 *
 * Execute
 *
 * phpunit --bootstrap <path-to-this-file> ...
 *
 * @package Test
 */

ini_set('include_path', ini_get('include_path') . ':' . realpath(dirname(__FILE__) . '/..'));

define('ENVIRONMENT', 'development');

require_once('Nethgui/Framework.php');
spl_autoload_register('Nethgui_Framework::autoloader');

require_once('Tool/ModuleTestCase.php');
require_once('Tool/ModuleTestEnvironment.php');
require_once('Tool/DB.php');
require_once('Tool/MockState.php');
require_once('Tool/MockObject.php');
require_once('Tool/Helpers.php');


