<?php
/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * TODO: describe class
 *
 * @package NethGuiFramework
 */
class NethGui_Core_ParameterSet implements NethGui_Core_AdapterAggregationInterface, ArrayAccess, Iterator, Countable
{

    private $data = array();
       

    /**
     * The number of members of this set.
     * @return integer
     */
    public function count()
    {
        return count($this->data);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset)
    {
        if(!$this->offsetExists($offset)) {
            trigger_error('Undefined offset `' . $offset . '`', E_USER_NOTICE);
            return NULL;
        }

        if ($this->data[$offset] instanceof NethGui_Core_AdapterInterface) {
            $value = $this->data[$offset]->get();
        } else {
            $value = $this->data[$offset];
        }

        return $value;
    }

    public function offsetSet($offset, $value)
    {
        if (isset($this->data[$offset]) && $this->data[$offset] instanceof NethGui_Core_AdapterInterface) {
            $this->data[$offset]->set($value);
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        if ($this->data[$offset] instanceof NethGui_Core_AdapterInterface) {
            $this->data[$offset]->delete();
        }
        unset($this->data[$offset]);
    }

    /**
     * Saves aggregated values into database, 
     * forwarding the call to Adapters and Sets.
     *
     * This is an helper function.
     */
    public function save()
    {
        foreach ($this->data as $value) {
            if ($value instanceof NethGui_Core_AdapterInterface) {
                $value->save();
            } elseif ($value instanceof NethGui_Core_AdapterAggregationInterface) {
                $value->save();
            }
        }
    }

    public function register(NethGui_Core_AdapterInterface $adapter, $key)
    {
        $this->data[$key] = $adapter;
    }

    
    public function current()
    {
        return $this->offsetGet(key($this->data));
    }

    public function key()
    {
        return key($this->data);
    }

    public function next()
    {
        next($this->data);
    }

    public function rewind()
    {
        reset($this->data);
    }

    public function valid()
    {
        return key($this->data) !== NULL;
    }

    /**
     * Converts the current instance to an array in the form key => value.
     * @return array
     */
    public function getArrayCopy() {
        $a = array();

        foreach($this as $key => $value){
            $a[$key] = $value;
        }

        return $a;
    }


}