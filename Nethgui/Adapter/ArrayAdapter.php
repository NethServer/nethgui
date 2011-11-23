<?php
/**
 * @package Adapter
 */

namespace Nethgui\Adapter;


/**
 * Array adapter maps a PHP array-like interface to a key or prop value
 * where values are separated by a separator character.
 *
 * @package Adapter
 */
class ArrayAdapter implements AdapterInterface, ArrayAccess, IteratorAggregate, Countable
{

    /**
     * The character used as separator to encode/decode the array string value.
     * @var string
     */
    private $separator;
    /**
     * This boolean is indeed a tri-state value, where NULL indicates
     * that object state is uninitialized.
     * @var boolean
     */
    private $modified;
    /**
     * Keeps the array values.
     * @var ArrayObject
     */
    private $data;
    /**
     *
     * @var \Nethgui\Serializer\SerializerInterface
     */
    private $serializer;

    public function __construct($separator, \Nethgui\Serializer\SerializerInterface $serializer)
    {
        $this->separator = $separator;
        $this->serializer = $serializer;
    }

    public function get()
    {
        if (is_null($this->modified)) {
            $this->lazyInitialization();
        }

        if (is_null($this->data)) {
            return NULL;
        }

        return $this->data;
    }

    public function set($value)
    {
        if(empty($value)) {
            $value = array();
        }
        
        if ( ! is_array($value) ) {
            throw new \Nethgui\Exception\Adapter('Invalid data type. Expected `array` or `EMPTY`, was ' . gettype($value));
        }

        if (is_null($this->modified)) {
            $this->lazyInitialization();
        }

        if (empty($value) && is_null($this->data))
        {
            $this->modified = FALSE;
            return;
        }

        if (empty($value) && !is_null($this->data))
        {
            $this->modified = TRUE;
            $this->data = NULL;
            return;
        }

        if (is_null($this->data)) {
            $this->data = new ArrayObject($value);
            $this->modified = TRUE;
        } elseif ($this->data->getArrayCopy() !== $value) {
            $this->data->exchangeArray($value);
            $this->modified = TRUE;
        }

    }

    public function delete()
    {
        $this->set(NULL);
    }

    public function isModified()
    {
        return $this->modified === TRUE;
    }

    public function save()
    {
        if ( ! $this->isModified()) {
            return 0;
        }

        if (is_object($this->data)) {
            $value = implode($this->separator, $this->data->getArrayCopy());
        } else {
            $value = NULL;
        }

        $this->serializer->write($value);

        $this->modified = FALSE;
        
        return 1;
    }

    public function count()
    {
        if (is_null($this->modified)) {
            $this->lazyInitialization();
        }

        if (is_null($this->data)) {
            return 0;
        }

        return count($this->data);
    }

    public function getIterator()
    {
        if (is_null($this->modified)) {
            $this->lazyInitialization();
        }

        if (is_null($this->data)) {
            return new ArrayIterator(array());
        }

        return $this->data->getIterator();
    }

    public function offsetExists($offset)
    {
        if (is_null($this->modified)) {
            $this->lazyInitialization();
        }
        return is_object($this->data) && $this->data->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        if (is_null($this->modified)) {
            $this->lazyInitialization();
        }

        if ($this->offsetExists($offset)) {
            return $this->data[$offset];
        }

        return NULL;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($this->modified)) {
            $this->lazyInitialization();
        }

        if (is_null($this->data)) {
            $this->data = new ArrayObject();
        }

        $this->data[$offset] = $value;
        $this->modified = TRUE;
    }

    public function offsetUnset($offset)
    {
        if (is_null($this->modified)) {
            $this->lazyInitialization();
        }

        if (isset($this->data[$offset])) {
            unset($this->data[$offset]);
            $this->modified = TRUE;
        }
    }

    private function lazyInitialization()
    {
        $value = $this->serializer->read();

        if (is_null($value)) {
            $this->data = NULL;
        } elseif ($value === '') {
            $this->data = new ArrayObject();
        } else
        {
            $this->data = new ArrayObject(explode($this->separator, $value));
        }

        $this->modified = FALSE;
    }

}
