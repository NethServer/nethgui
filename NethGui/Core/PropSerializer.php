<?php

/**
 * @package
 */
class NethGui_Core_PropSerializer implements NethGui_Core_SerializerInterface
{
    private $key;
    private $prop;

    /**
     *
     * @var NethGui_Core_ConfigurationDatabase
     */
    private $database;

    public function __construct(NethGui_Core_ConfigurationDatabase $database, $key, $prop)
    {
        $this->database = $database;
        $this->key = $key;
        $this->prop = $prop;
    }

    public function read()
    {
        return $this->database->getProp($this->key, $this->prop);
    }

    public function write($value)
    {
        $this->database->setProp($key, array($this->prop => $value));
    }

}