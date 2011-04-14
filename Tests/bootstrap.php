<?php
/**
 * @package Tests
 */

ini_set('include_path', ini_get('include_path') . ':' . realpath(dirname(__FILE__) . '/..'));

require_once('NethGui/Framework.php');
require_once('ModuleTestCase.php');

spl_autoload_register('NethGui_Framework::autoloader');
