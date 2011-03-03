<?php

function nethgui_tests_autoloader($className)
{
    $classPath = str_replace("_", "/", $className) . '.php';
    require_once($classPath);
}

spl_autoload_register('nethgui_tests_autoloader');

ini_set('include_path', ini_get('include_path') . ':' . realpath(dirname(__FILE__) . '/..'));
