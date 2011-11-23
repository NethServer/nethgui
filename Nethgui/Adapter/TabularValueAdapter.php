<?php
/**
 * @package Adapter
 * @author Davide Principi <davide.principi@nethesis.it>
 */

namespace Nethgui\Adapter;

/**
 * The TabularValueAdapter provide an array interface to access tabular data 
 * encoded into a value stored in a key or prop.
 * 
 * The encoding uses a separator string to split the logical rows and 
 * another separator string to separate logical columns.
 * 
 * This is implemented applying a decorator pattern to ArrayAdapter
 *
 * @package Adapter
 */
class TabularValueAdapter implements AdapterInterface, \ArrayAccess, \IteratorAggregate, \Countable
{

    /**
     * @var ArrayAdapter
     */
    private $innerAdapter;
    /**
     *
     * @var ArrayObject
     */
    private $data;
    private $modified;
    private $columnSeparator;

    public function __construct(ArrayAdapter $innerAdapter, $columnSeparator)
    {
        $this->innerAdapter = $innerAdapter;
        $this->columnSeparator = $columnSeparator;
    }

    private function lazyInitialization()
    {
        $this->data = new ArrayObject();

        foreach ($this->innerAdapter as $rawRow) {
            $row = explode($this->columnSeparator, $rawRow);            
            $key = array_shift($row);            
            $this->data[$key] = $row;
        }

        $this->modified = FALSE;
    }

    public function count()
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        return $this->data->count();
    }

    public function delete()
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }
        
        if (count($this->data) != 0) {
            $this->modified = TRUE;
            $this->data = new ArrayObject();
        }
        
    }

    public function get()
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        return $this->data;
    }

    public function set($value)
    {
        $this->data = new ArrayObject();
        $this->modified = TRUE;

        if ( ! is_array($value) && ! $value instanceof Traversable) {
            throw new InvalidArgumentException('Value must be an array!');
        }

        foreach ($value as $key => $row) {
            if ( ! is_array($row)) {
                throw new InvalidArgumentException('Value must be composed of arrays!');
            }
            $this[$key] = $row;
        }
    }

    public function save()
    {
        if ( ! $this->isModified()) {
            return 0;
        }

        $value = array();
        
        foreach($this->data as $key => $row) {
            $value[] = implode($this->columnSeparator, array_merge(array($key), $row));
        }
        
        $this->innerAdapter->set($value);
        
        $saveCount = $this->innerAdapter->save();

        $this->modified = FALSE;

        return $saveCount;
    }

    public function getIterator()
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        return $this->data->getIterator();
    }

    public function isModified()
    {
        return $this->modified === TRUE;
    }

    public function offsetExists($offset)
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        return $this->data->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        return $this->data->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        if ( ! is_array($value) && ! $value instanceof Traversable) {
            throw new InvalidArgumentException('Value must be an array!');
        }

        $this->modified = TRUE;

        $this->data->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        if ($this->data->offsetExists($offset)) {
            unset($this->data[$offset]);
            $this->modified = TRUE;
        }
    }

}

