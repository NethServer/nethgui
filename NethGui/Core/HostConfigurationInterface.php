<?php
/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * HostConfigurationInterface
 *
 * An HostConfigurationInterface implementing object allows reading and changing
 * the current host machine configuration.
 *
 * Access to a certain configuration values is defined by an array of keys.
 *
 * @package NethGuiFramework
 */
interface HostConfigurationInterface {

    /**
     * @param array $resourcePath
     */    
    public function read($resourcePath);

    /**
     * @param array $resourcePath
     * @param string $value
     */
    public function write($resourcePath, $value);

    /**
     * @return bool FALSE on failure
     */
    public function commit();
}

