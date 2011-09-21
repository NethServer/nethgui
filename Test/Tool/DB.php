<?php
/**
 * @package Test
 * @subpackage Tool
 */

/**
 * @package Test
 * @subpackage Tool
 * @author Davide Principi <davide.principi@nethesis.it>
 * @see Test_Tool_ModuleTestCase
 */
class Test_Tool_DB
{

    public static function getKey($key)
    {
        return array('getType', array($key));
    }

    public static function setKey($key, $value)
    {
        return array('setType', array($key, $value));
    }

    public static function getProp($key, $prop)
    {
        return array('getProp', array($key, $prop));
    }

    public static function setProp($key, $prop, $value)
    {
        return array('setProp', array($key, array($prop => $value)));
    }

}