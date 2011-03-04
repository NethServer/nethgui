<?php

/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * HostConfigurationInterface
 *
 * An NethGui_Core_HostConfigurationInterface implementing object allows reading and changing
 * the current host machine configuration.
 *
 * Access to a certain configuration values is defined by an array of keys.
 *
 * @package NethGuiFramework
 */
interface NethGui_Core_HostConfigurationInterface
{

    public function __construct($db);

    /**
    * /sbin/e-smith/db dbfile get key
    */
    public function getKey($key);
   
    /** 
    * /sbin/e-smith/db dbfile set key type [prop1 val1] [prop2 val2] ...
    */
    public function setKey($key,$type,$props);

    /** 
    * /sbin/e-smith/db dbfile delete key
    */ 
    public function deleteKey($key);

    /**
    * /sbin/e-smith/db dbfile gettype key
    */
    public function getType($key);
    
    /**
    * /sbin/e-smith/db dbfile settype key type
    */
    public function setType($key,$type);
   
    /**
    * /sbin/e-smith/db dbfile getprop key prop
    */
    public function getProp($key,$prop); 
  
    /**
    * /sbin/e-smith/db dbfile setprop key prop1 val1 [prop2 val2] [prop3 val3] ...
    */
    public function setProp($key,$props);  

    /**
    * /sbin/e-smith/db dbfile delprop key prop1 [prop2] [prop3] ...
    */
    public function delProp($key,$props);  

}

