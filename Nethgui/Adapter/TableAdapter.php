<?php
/**
 * @package Adapter
 * @author Davide Principi <davide.principi@nethesis.it>
 */

namespace Nethgui\Adapter;

/**
 * Table adapter provide an array like access to all database keys of a given type
 *
 * @package Adapter
 */
class TableAdapter implements AdapterInterface, \ArrayAccess, \IteratorAggregate, \Countable
{

    /**
     *
     * @var \Nethgui\System\ConfigurationDatabase
     */
    private $database;
    private $type;
    private $filter;
    /**
     *
     * @var ArrayObject
     */
    private $data;
    /**
     *
     * @var ArrayObject
     */
    private $changes;

    /**
    *
    * @db string Database used for table mapping
    * @type string Type of the key for mapping
    * @filter mixed Can be a string or an associative array. When using a string, filter is a fulltext search on db keys, otherwise it's an array in the form ('prop1'=>'val1',...,'propN'=>'valN') where valN it's a regexp. In this case, the adapter will return only rows where all props match all associated regexp.
    *
    **/
    public function __construct(\Nethgui\System\ConfigurationDatabase $db, $type, $filter = FALSE)
    {
        $this->database = $db;
        $this->type = $type;
        $this->filter = $filter;
    }

    private function filterMatch($value)
    {
        foreach($this->filter as $prop=>$regexp) {
             if(!preg_match($regexp,$value[$prop])) {
                 return false;
             }
        }
        return true;
    }

    private function lazyInitialization()
    {
        $this->data = new \ArrayObject();
       
        if(is_array($this->filter)) { #apply simple filter only if filter is a string
            $rawData =$this->database->getAll($this->type); 
            if(is_array($rawData)) {
                // skip the first column, where getAll() returns the key type.
                foreach($rawData as $key => $row) {
                    if($this->filterMatch(array_slice($row, 1))) {
                        $this->data[$key] = array_slice($row, 1);
                    }
                }
            }
        } else {
            $rawData =$this->database->getAll($this->type, $this->filter);
            foreach($rawData as $key => $row) {
                $this->data[$key] = array_slice($row, 1);
            }

        }
                
        $this->changes = new \ArrayObject();
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
                      
        foreach (array_keys($this->data->getArrayCopy()) as $key) {
            unset($this[$key]);
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
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        if ( ! is_array($value) && ! $value instanceof Traversable) {
            throw new InvalidArgumentException('Value must be an array!');
        }

        foreach ($value as $key => $props) {
            $this[$key] = $props;
        }
    }

    public function save()
    {
        if ( ! $this->isModified()) {
            return 0;
        }

        $saveCount = 0;

        foreach ($this->changes as $args) {
            $method = array_shift($args);
            call_user_func_array(array($this->database, $method), $args);
            $saveCount ++;
        }

        $this->changes = new \ArrayObject();
        
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
        return $this->changes instanceof \ArrayObject && count($this->changes) > 0;
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

        if (isset($this[$offset])) {
            $this->changes[] = array('setProp', $offset, $value);
        } else {
            $this->changes[] = array('setKey', $offset, $this->type, $value);
        }

        $this->data->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        unset($this->data[$offset]);
        $this->changes[] = array('deleteKey', $offset);
    }

}
