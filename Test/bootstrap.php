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


require_once(realpath(dirname(__FILE__) . '/../') . '/Nethgui/Framework.php');

$FW = new \Nethgui\Framework();
$FW
    ->registerNamespace('Test')
    ->registerNamespace('NethServer')
    ->setSiteUrl('http://localhost:8080');





