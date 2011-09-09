<?php
/**
 * @package Serializer
 */

/**
 * Transfer a value to/from a database Key.
 *
 * @package Serializer
 */
class Nethgui_Serializer_KeySerializer implements Nethgui_Serializer_SerializerInterface
{

    private $key;
    /**
     *
     * @var Nethgui_Core_ConfigurationDatabase
     */
    private $database;

    public function __construct(Nethgui_Core_ConfigurationDatabase $database, $key)
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