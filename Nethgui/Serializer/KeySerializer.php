<?php
/**
 */

namespace Nethgui\Serializer;

/**
 * Transfer a value to/from a database Key.
 *
 */
class KeySerializer implements SerializerInterface
{

    private $key;
    /**
     *
     * @var \Nethgui\System\ConfigurationDatabase
     */
    private $database;

    public function __construct(\Nethgui\System\ConfigurationDatabase $database, $key)
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
