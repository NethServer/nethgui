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
date_default_timezone_set('UTC');

if ( ! defined('NETHGUI_FILE')) {
    exit("Bootstrap: NETHGUI_FILE is not defined.");
}

if ( ! defined('NETHGUI_APPLICATION')) {
    exit("Bootstrap: NETHGUI_APPLICATION is not defined.");
}

if ( ! defined('NETHGUI_ENVIRONMENT')) {
    exit("Bootstrap: NETHGUI_ENVIRONMENT is not defined.");
}

// Find the root directory, where Nethgui/ and APPLICATION dirs are placed:
define('NETHGUI_ROOTDIR', realpath(dirname(__FILE__) . '/..'));

if ( ! NETHGUI_ROOTDIR) {
    exit("Bootstrap: Failed in setting NETHGUI_ROOTDIR");
}

ini_set('include_path', ini_get('include_path') . ':' . NETHGUI_ROOTDIR);

if ( ! defined('NETHGUI_NATIVE')) {
    // Default to FALSE: enable CodeIgniter framework
    define('NETHGUI_NATIVE', FALSE);
}

if ( ! defined('NETHGUI_CONTROLLER')) {
    // This is the FE controller URL path fragment
    define('NETHGUI_CONTROLLER', NETHGUI_NATIVE ? basename(NETHGUI_FILE) : basename(NETHGUI_FILE) . '/dispatcher');
}

if ( ! defined('NETHGUI_INDEX')) {
    // Set to the default application module identifier:
    define('NETHGUI_INDEX', FALSE);
}


if ( ! defined('NETHGUI_BASEURL')) {
    $f = function ($scriptName)
        {
            $parts = array_values(array_filter(explode('/', $scriptName)));
            $lastPart = $parts[max(0, count($parts) - 1)];
            $nethguiFile = basename(NETHGUI_FILE);

            if ($lastPart == $nethguiFile) {
                array_pop($parts);
            }
            return '/' . implode('/', $parts) . '/';
        };

    // This is the prefix to any Nethgui-generated URL
    define('NETHGUI_BASEURL', $f($_SERVER['SCRIPT_NAME']));
    unset($f);
}

switch (NETHGUI_ENVIRONMENT) {
    case 'development':
        //error_reporting(E_ALL | E_STRICT);
        error_reporting(E_ALL);
        break;

    case 'test':
        error_reporting(E_ALL | E_STRICT);
        break;

    case 'production':
        error_reporting(0);
        break;

    default:
        exit('Bootstrap: NETHGUI_ENVIRONMENT is not set correctly.');
}



if ( ! defined('NETHGUI_CUSTOMCSS')) {
    define('NETHGUI_CUSTOMCSS', FALSE);
}

if (defined('STDIN')) {
    define('NETHGUI_SITEURL', FALSE);
} else {
    define('NETHGUI_SITEURL', (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT']);
    if (parse_url(NETHGUI_SITEURL) === FALSE) {
        die('Invalid site NETHGUI_SITEURL');
    }
}

require_once('Framework.php');

if (NETHGUI_APPLICATION == 'Test') {
    $FW = new Nethgui_Framework();
    return;
} elseif (NETHGUI_NATIVE) {
    $_nethgui_app = function() {
            $urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $suffix = substr($urlPath, strlen(NETHGUI_BASEURL . NETHGUI_CONTROLLER) + 1);
            $parts = explode('/', $suffix);

            $FW = new Nethgui_Framework();
            $FW->dispatch(empty($parts[0]) ? 'index' : $parts[0], array_slice($parts, 1));
        };

    return $_nethgui_app();
}

/**
 * Start CodeIgniter framework
 */
// Set the current directory correctly for CLI requests
if (defined('STDIN')) {
    chdir(dirname(NETHGUI_FILE));
}

// ENVIRONMENT is used by CodeIgniter and takes the same value:
define('ENVIRONMENT', NETHGUI_ENVIRONMENT);

$system_path = realpath('../CodeIgniter/system');
$application_folder = realpath('../CodeIgniter/application');

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
if (is_dir($application_folder)) {
    define('APPPATH', $application_folder . '/');
} else {
    if ( ! is_dir(BASEPATH . $application_folder . '/')) {
        exit("Your application folder path does not appear to be set correctly. Please open the following file and correct this: " . SELF);
    }

    define('APPPATH', BASEPATH . $application_folder . '/');
}

require_once BASEPATH . 'core/CodeIgniter' . EXT;


