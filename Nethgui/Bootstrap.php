<?php
/**
 * Nethgui Framework
 *
 * (C) 2011 Nethesis S.r.l.
 *
 * Common config values to bootstrap Nethgui 
 *
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


if ( ! defined('NETHGUI_SITEURL')) {
    if (defined('STDIN')) {
        define('NETHGUI_SITEURL', FALSE);
    } else {
        $tmpSiteUrl = empty($_SERVER['HTTPS']) ? 'http://' : 'https://';
        $tmpSiteUrl .= $_SERVER['SERVER_NAME'];
        if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80') {
            $tmpSiteUrl .= ':' . $_SERVER['SERVER_PORT'];
        }
        define('NETHGUI_SITEURL', $tmpSiteUrl);
        unset($tmpSiteUrl);
    }
}

if (parse_url(NETHGUI_SITEURL) === FALSE) {
    print_r($_SERVER);
    die(sprintf('Invalid NETHGUI_SITEURL constant "%s"', NETHGUI_SITEURL));

}

require_once('Tool.php');
require_once('Framework.php');

if (NETHGUI_APPLICATION == 'Test') {
    $FW = new \Nethgui\Framework();
    $FW->registerApplication(NETHGUI_ROOTDIR . '/Test');
    $FW->registerApplication(NETHGUI_ROOTDIR . '/NethServer');   
    return;
} elseif (NETHGUI_NATIVE) {
    $_nethgui_app = function() {
            $urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $suffix = substr($urlPath, strlen(NETHGUI_BASEURL . NETHGUI_CONTROLLER) + 1);
            $parts = explode('/', $suffix);

            $FW = new \Nethgui\Framework();
            $FW->registerApplication(NETHGUI_ROOTDIR . '/' . NETHGUI_APPLICATION);
            $FW->dispatch(empty($parts[0]) ? 'index' : $parts[0], array_slice($parts, 1));
        };

    return $_nethgui_app();
}
