<?php
/**
 * Nethgui Framework
 *
 * (C) 2011 Nethesis S.r.l.
 *
 * Common config values to bootstrap CodeIgniter and Nethgui 
 *
 * @ignore
 */
if ( ! defined('NETHGUI_FILE')) {
    exit("Bootstrap: NETHGUI_FILE is not defined.");
}

if ( ! defined('NETHGUI_APPLICATION')) {
    exit("Bootstrap: NETHGUI_APPLICATION is not defined.");
}

if ( ! defined('NETHGUI_ENVIRONMENT')) {
    exit("Bootstrap: NETHGUI_ENVIRONMENT is not defined.");
}

switch (NETHGUI_ENVIRONMENT) {
    case 'development':
        error_reporting(E_ALL | E_STRICT);
        break;

    case 'testing':
    case 'production':
        error_reporting(0);
        break;

    default:
        exit('Bootstrap: NETHGUI_ENVIRONMENT is not set correctly.');
}

define('ENVIRONMENT', NETHGUI_ENVIRONMENT);

define('NETHGUI_SITEURL', (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') .  $_SERVER['SERVER_NAME']);

if (parse_url(NETHGUI_SITEURL) === FALSE) {
    die('Invalid site URL');
}

require_once('Framework.php');

$system_path = realpath('../CodeIgniter/system');
$application_folder = realpath('../CodeIgniter/application');
date_default_timezone_set('UTC');

// Set the current directory correctly for CLI requests
if (defined('STDIN')) {
    chdir(dirname(NETHGUI_FILE));
}

if (realpath($system_path) !== FALSE) {
    $system_path = realpath($system_path) . '/';
}

// ensure there's a trailing slash
$system_path = rtrim($system_path, '/') . '/';

// Is the system path correct?
if ( ! is_dir($system_path)) {
    exit("Your system folder path does not appear to be set correctly. Please open the following file and correct this: " . pathinfo(NETHGUI_FILE, PATHINFO_BASENAME));
}

/*
 * -------------------------------------------------------------------
 *  Now that we know the path, set the main path constants
 * -------------------------------------------------------------------
 */
// The name of THIS file
if ( ! defined('SELF')) {
    define('SELF', pathinfo(NETHGUI_FILE, PATHINFO_BASENAME));
}

// The PHP file extension
define('EXT', '.php');

// Path to the system folder
define('BASEPATH', str_replace("\\", "/", $system_path));

// Path to the front controller 
define('FCPATH', str_replace(SELF, '', NETHGUI_FILE));

// Name of the "system folder"
define('SYSDIR', trim(strrchr(trim(BASEPATH, '/'), '/'), '/'));


// The path to the "application" folder
if (is_dir($application_folder))
{
    define('APPPATH', $application_folder . '/');
} else
{
    if ( ! is_dir(BASEPATH . $application_folder . '/'))
    {
        exit("Your application folder path does not appear to be set correctly. Please open the following file and correct this: " . SELF);
    }

    define('APPPATH', BASEPATH . $application_folder . '/');
}

require_once BASEPATH . 'core/CodeIgniter' . EXT;


