<?php
/**
 * PHPUnit bootstrap file
 *
 * Execute
 *
 * phpunit --bootstrap <path-to-this-file> ...
 *
 */

// Some PHP settings:
date_default_timezone_set('UTC');
error_reporting(E_ALL | E_STRICT);

// If xdebug is loaded, disable xdebug backtraces:
extension_loaded('xdebug') && xdebug_disable();

require_once('vendor/autoload.php');


$namespaces = array();
$nsbase = __DIR__;
$loader = new \Composer\Autoload\ClassLoader();
$loader->add('Nethgui', $nsbase);
$loader->add('NethServer', $nsbase);
$loader->register();
foreach ($loader->getPrefixes() as $nsName => $paths) {
    $namespaces[trim($nsName, '\\')] = reset($paths) . DIRECTORY_SEPARATOR . trim($nsName, '\\');
}
$loader->add('Pimple', $nsbase);
$loader->add('Mustache', $nsbase);
$loader->add('Symfony', $nsbase);
$FW = new \Nethgui\Framework();
$FW
    ->setLogLevel(E_WARNING | E_ERROR | E_NOTICE)
    ->setSiteUrl('http://localhost:8080')
;
$FW=NULL;
