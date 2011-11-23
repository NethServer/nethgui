<?php
/**
 * @package Serializer
 */

namespace Nethgui\Serializer;

/**
 * Transfers a prop value to/from an object implementing ArrayAccess interface
 *
 * @package Serializer
 * @see Nethgui\Module\Table\Modify
 */
class ArrayAccessSerializer implements SerializerInterface
{

    private $prop;
    private $key;
    /**
     *
     * @var ArrayAccess
     */
    private $table;

    public function __construct(ArrayAccess $table, $key, $prop)
    {
        $this->table = $table;
        $this->key = $key;

        if (is_null($prop) || $prop == '' || $prop === FALSE) {
            throw new InvalidArgumentException('The `prop` argument is invalid');
        }

        $this->prop = strval($prop);
    }

    public function read()
    {
        if ( ! $this->table->offsetExists($this->key)) {
            return NULL;
        }

        $record = $this->table->offsetGet($this->key);
        if ( ! isset($record[$this->prop])) {
            return NULL;
        }
        return $record[$this->prop];
    }

    public function write($value)
    {
        if ( ! isset($this->key)) {
            throw new Nethgui\Exception\Serializer('The TablePropSerializer `key` is not missing.');
        }

        // update or append ?
        if ($this->table->offsetExists($this->key)) {
            $record = $this->table->offsetGet($this->key);
        } else {
            $record = array();
        }

        $record[$this->prop] = $value;
        $this->table->offsetSet($this->key, $record);
    }

}
