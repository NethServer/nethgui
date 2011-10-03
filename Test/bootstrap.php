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


require_once('Nethgui/Framework.php');
require_once('Tool/Helpers.php');

define('NETHGUI_ENVIRONMENT', 'development');
define('ENVIRONMENT', 'development');

// this installs the autoloader function:
Nethgui_Framework::getInstance();




