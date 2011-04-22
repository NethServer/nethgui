<?php
/**
 * @package Core
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * Table adapter provide an array like access to all database keys of a given type
 *
 * @package Core
 */
class NethGui_Core_TableAdapter implements NethGui_Core_AdapterInterface, ArrayAccess, IteratorAggregate, Countable
{

    /**
     *
     * @var NethGui_Core_ConfigurationDatabase
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

    public function __construct(NethGui_Core_ConfigurationDatabase $db, $type, $filter = FALSE)
    {
        $this->database = $db;
        $this->type = $type;
        $this->filter = $filter;
    }

    private function lazyInitialization()
    {
        $this->data = new ArrayObject($this->database->getAll($this->type, $this->filter));
        $this->changes = new ArrayObject();
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

        foreach (array_keys($this->data)as $key) {
            unset($this[$key]);
        }
    }

    public function get()
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        return $this;
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
        if ($this->isModified()) {
            foreach ($this->changes as $args) {
                $method = array_shift($args);
                call_user_func_array(array($this->database, $method), $args);
            }

            $this->changes = new ArrayObject();
        }
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
        return $this->changes instanceof ArrayObject && count($this->changes) > 0;
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

        $this->data->offsetUnset($offset);
        $this->changes[] = array('deleteKey', $offset);
    }

}