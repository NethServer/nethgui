<?php
/**
 * NethGui
 *
 * @package Core
 */

/**
 * Holds primitive and adapter-embedded values.
 * 
 * It propagates the save() message to all the members of the set.  
 * Inside a ParameterSet you can store:
 *
 * - Primitive values
 * - Adapter objects
 * - Other objects implementing AdapterAggregationInterface
 *
 * @package Core
 */
class NethGui_Core_ParameterSet implements NethGui_Adapter_AdapterAggregationInterface, ArrayAccess, Iterator, Countable
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
        if ( ! $this->offsetExists($offset)) {
            trigger_error('Undefined offset `' . $offset . '`', E_USER_NOTICE);
            return NULL;
        }

        if ($this->data[$offset] instanceof NethGui_Adapter_AdapterInterface) {
            $value = $this->data[$offset]->get();
        } else {
            $value = $this->data[$offset];
        }

        return $value;
    }

    public function offsetSet($offset, $value)
    {
        if (isset($this->data[$offset]) && $this->data[$offset] instanceof NethGui_Adapter_AdapterInterface) {
            $this->data[$offset]->set($value);
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        if ($this->data[$offset] instanceof NethGui_Adapter_AdapterInterface) {
            $this->data[$offset]->delete();
        }
        unset($this->data[$offset]);
    }

    /**
     * Saves aggregated values into database, 
     * forwarding the call to Adapters and Sets.
     *
     * This is an helper function.
     * @see NethGui_Adapter_AdapterAggregationInterface::save()
     * @return integer The number of saved parameters. A zero value indicates that nothing has been saved.
     */
    public function save()
    {
        $saveCounter = 0;

        foreach ($this->data as $value) {
            if ($value instanceof NethGui_Adapter_AdapterInterface) {
                $saveCounter += $value->save();
            } elseif ($value instanceof NethGui_Adapter_AdapterAggregationInterface) {
                $saveCounter += $value->save();
            }
        }

        return $saveCounter;
    }

    public function register(NethGui_Adapter_AdapterInterface $adapter, $key)
    {
        $this->data[$key] = $adapter;
    }

    public function isModified($key = NULL)
    {
        if (is_null($key)) {
            $keys = array_keys($this->data);
        } else {
            $keys = array($key);
        }

        foreach ($keys as $key) {
            $value = $this->data[$key];
            
            if ($value instanceof NethGui_Adapter_AdapterInterface) {
                $modified = $value->isModified();
            } elseif ($value instanceof NethGui_Adapter_AdapterAggregationInterface) {
                $modified = $value->isModified(NULL);
            }

            if ($modified === TRUE) {
                return TRUE;
            }
        }

        return FALSE;      
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
    public function getArrayCopy()
    {
        $a = array();

        foreach ($this as $key => $value) {
            $a[$key] = $value;
        }

        return $a;
    }

}