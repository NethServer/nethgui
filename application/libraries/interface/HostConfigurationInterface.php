<?php

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

