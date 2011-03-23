<?php


class NethGui_Core_ArrayAdapter implements NethGui_Core_AdapterInterface, ArrayAccess, IteratorAggregate, Countable {
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
        parent::__construct();
        $this->separator = $separator;
        $this->serializer = $serializer;
    }

    public function get()
    {
        if(is_null($this->data)) {
            
        }
        
    }

    public function set($value)
    {
        if(is_null($this->data)) {
            
        }
     
    }

    public function delete()
    {
        
    }

    public function isModified()
    {
        return $this->modified === TRUE;
    }

    public function save()
    {
        if( ! $this->isModified()) {
            return;
        }

        $this->modified = FALSE;
    }

}
