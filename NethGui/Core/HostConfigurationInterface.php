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
 * Access to a certain configuration values is defined by database, keys and properties.
 *
 * @package NethGuiFramework
 */
interface NethGui_Core_HostConfigurationInterface
{

    /**
     * Set the working database 
     * 
     * @param string $db Database name
     * @access public
     * @return NethGui_Core_HostConfigurationInterface
     */
    public function setDB($db);

    /**
     * Retrieve a key from the database. 
     *
     * @param string $key the key to read
     * @access public
     * @return array associative array in the form [PropName] => [PropValue]
     */
    public function getKey($key);
   
    /**
     * Set a database key with type and properties.
     * 
     * @param string $key Key to write
     * @param string $type Type of the key
     * @param string $props Array of properties in the form  [PropName] => [PropValue]
     * @access public
     * @return bool TRUE on success, FALSE otherwise
     *
     */
    public function setKey($key,$type,$props);

    /**
     * Delete a key and all its properties 
     * 
     * @param mixed $key 
     * @access public
     * @return void
     */
    public function deleteKey($key);

    /**
     * Return the type of a key
     * Act like: /sbin/e-smith/db dbfile gettype key
     * 
     * @param string $key the key to retrieve
     * @access public
     * @return string the type of the key
     */
    public function getType($key);
   
    /**
     * Set the type of a key 
     * 
     * @param string $key the key to change
     * @param string $type the new type
     * @access public
     * @return bool true on success, FALSE otherwise
     */
    public function setType($key,$type);
  
    /**
     * Read the value of the given property
     * 
     * @param string $key the parent property key
     * @param string $prop the name of the property
     * @access public
     * @return string the value of the property
     */
    public function getProp($key,$prop); 
 
    /**
     * Set one or more properties under the given key
     * 
     * @param string $key the property parent key
     * @param array $props an associative array in the form [PropName] => [PropValue]  
     * @access public
     * @return bool TRUE on success, FALSE otherwise
     */
    public function setProp($key,$props);  

    /**
     * Delete one or more properties under the given key 
     * 
     * @param string $key the property parent key
     * @param array $props a simple array containg the properties to be deleted
     * @access public
     * @return bool TRUE on success, FALSE otherwise
     */
    public function delProp($key,$props);  

}

