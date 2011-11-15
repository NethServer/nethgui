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


define('NETHGUI_ENVIRONMENT', 'development');
define('NETHGUI_APPLICATION', 'Test');
define('NETHGUI_FILE', __FILE__);
define('NETHGUI_NATIVE', TRUE);

require_once('Tool/Helpers.php');
require_once(realpath(dirname(__FILE__) . '/../') . '/Nethgui/Bootstrap.php');



