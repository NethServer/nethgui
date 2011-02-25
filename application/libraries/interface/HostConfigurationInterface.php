<?php

interface HostConfigurationInterface {
    public function read($resource);
    public function write($resource, $value);
    public function apply();
}

