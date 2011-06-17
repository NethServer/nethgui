<?php
/**
 * @package Serializer
 */

/**
 * Transfers a prop value to/from a table adapter
 *
 * @package Serializer
 * @see NethGui_Module_Table_Modify
 */
class NethGui_Serializer_TablePropSerializer implements NethGui_Serializer_SerializerInterface
{
    private $prop;
    private $key;
    
    /**
     *
     * @var ArrayAccess
     */
    private $tableAdapter;
 
    public function __construct(ArrayAccess $adapter, $key, $prop)
    {
        $this->tableAdapter = $adapter;
        $this->key = $key;
        $this->prop = $prop;
    }    

    public function read()
    {       
        $record = $this->tableAdapter->offsetGet($this->key);
        if(!isset($record[$this->prop])) {
            return NULL;        
        }
        return $record[$this->prop];
    }

    public function write($value)
    {
        if(!isset($this->key)) {
            throw new NethGui_Exception_Serializer('The TablePropSerializer `key` is not missing.');
        }
        
        $record = $this->tableAdapter->offsetGet($this->key);
        $record[$this->prop] = $value;
        $this->tableAdapter->offsetSet($this->key, $record);
    }

}