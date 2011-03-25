<?php

class NethGui_Core_KeySerializer implements NethGui_Core_SerializerInterface
{

    private $key;
    /**
     *
     * @var NethGui_Core_ConfigurationDatabase
     */
    private $database;

    public function __construct(NethGui_Core_ConfigurationDatabase $database, $key)
    {
        $this->database = $database;
        $this->key = $key;
    }

    /**
     * XXX: Calling "getType" for reading key value (?)
     * @return string
     */
    public function read()
    {
        return $this->database->getType($this->key);
    }

    /**
     * XXX: Calling "setType" for writing key value (?)
     * @return string
     */
    public function write($value)
    {
        if($value === NULL){
            $this->database->deleteKey($this->key);
        } else {
            $this->database->setType($this->key, strval($value));
        }
    }

}