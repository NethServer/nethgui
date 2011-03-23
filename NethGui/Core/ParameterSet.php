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
class NethGui_Core_ParameterSet implements ArrayAccess, IteratorAggregate, Countable
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

    /**
     * An iterator for all memebers of this set.
     * @return Iterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset)
    {
        if ($this->data[$offset] instanceof NethGui_Core_AdapterInterface) {
            $value = $this->data[$offset]->get();
        } else {
            $value = $this->data[$offset];
        }

        return $value;
    }

    public function offsetSet($offset, $value)
    {
        if ($value instanceof NethGui_Core_AdapterInterface) {
            // TODO check if substituting another object.
            $this->data[$offset] = $value;
        } elseif (isset($this->data[$offset]) && $this->data[$offset] instanceof NethGui_Core_AdapterInterface) {
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
    public function save() {
        foreach($this->data as $value) {
            if($value instanceof NethGui_Core_AdapterInterface) {
                $value->save();
            } elseif ($value instanceof NethGui_Core_ParameterSet) {
                $value->save();
            }
        }
    }

}