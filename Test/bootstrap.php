<?php
/**
 * PHPUnit bootstrap file
 *
 * Execute
 *
 * phpunit --bootstrap <path-to-this-file> ...
 *
 */


define('NETHGUI_ENVIRONMENT', 'test');
define('NETHGUI_APPLICATION', 'Test');
define('NETHGUI_FILE', __FILE__);
define('NETHGUI_NATIVE', TRUE);
define('NETHGUI_SITEURL', 'http://localhost:8080');

require_once('Tool/Helpers.php');
require_once(realpath(dirname(__FILE__) . '/../') . '/Nethgui/Bootstrap.php');




