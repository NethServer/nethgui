<?php
/**
 * @package Serializer
 */

namespace Nethgui\Serializer;

/**
 * Transfers a value to/from a database Prop.
 *
 * @package Serializer
 */
class PropSerializer implements SerializerInterface
{
    private $key;
    private $prop;

    /**
     *
     * @var \Nethgui\System\ConfigurationDatabase
     */
    private $database;

    public function __construct(\Nethgui\System\ConfigurationDatabase $database, $key, $prop)
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
        if($value === NULL) {
            $this->database->delProp($this->key, array($this->prop));
        } else {
            $this->database->setProp($this->key, array($this->prop => $value));
        }
    }

}
