<?php

class NethGui_Core_ArrayAdapter implements NethGui_Core_AdapterInterface, ArrayAccess, IteratorAggregate, Countable
{

    /**
     *
     * @var string
     */
    private $separator;
    private $modified;
    private $data;
    /**
     *
     * @var NethGui_Core_SerializerInterface
     */
    private $serializer;

    public function __construct($separator, NethGui_Core_SerializerInterface $serializer)
    {
        $this->separator = $separator;
        $this->serializer = $serializer;
    }

    public function get()
    {
        if (is_null($this->modified)) {
            $this->lazyInitialization();
        }

        return $this;
    }

    public function set($value)
    {
        if ( ! is_array($value) && ! is_null($value)) {
            throw new NethGui_Exception_Adapter('Invalid data type. Expected `array` or `NULL`, was ' . gettype($value));
        }

        if (is_null($this->modified)) {
            $this->lazyInitialization();
        }

        if ($this->data !== $value) {
            $this->modified = TRUE;
            $this->data = $value;
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
            return;
        }

        if (is_array($this->data)) {
            $value = implode($this->separator, $this->data);
        } else {
            $value = NULL;
        }

        $this->serializer->write($value);

        $this->modified = FALSE;
    }

    public function count()
    {
        if (is_null($this->modified)) {
            $this->lazyInitialization();
        }

        return count($this->data);
    }

    public function getIterator()
    {
        if (is_null($this->modified)) {
            $this->lazyInitialization();
        }

        if(is_null($this->data)) {
            return new ArrayIterator(array());
        }
        
        return new ArrayIterator($this->data);
    }

    public function offsetExists($offset)
    {
        if (is_null($this->modified)) {
            $this->lazyInitialization();
        }
        return is_array($this->data) && array_key_exists($offset, $this->data);
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

        if(is_null($this->data)) {
            $this->data = array();
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
            $this->data = array();
        } else {
            $this->data = explode($this->separator, $value);
        }

        $this->modified = FALSE;
    }

}
