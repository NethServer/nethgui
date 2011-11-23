<?php
/**
 * @package Core
 */

/**
 * @todo Describe class...
 * @package Core
 * @internal
 */
final class Nethgui\Core\ModuleMenuIterator implements RecursiveIterator
{

    private $elements;
    /**
     * @var ModuleSetInterface
     */
    private $moduleSet;
    private $pointer;
    private $key;


    public function __construct(Nethgui\Core\ModuleSetInterface $moduleSet, $pointer, &$elements = array())
    {
        $this->elements = $elements;
        $this->pointer = $pointer;
        $this->moduleSet = $moduleSet;
    }

    public function current()
    {
        return $this->moduleSet->findModule($this->currentIdentifier());
    }

    private function currentIdentifier()
    {
        return $this->elements[$this->pointer][$this->key];
    }

    public function getChildren()
    {
        return new self($this->moduleSet, $this->currentIdentifier(), $this->elements);
    }

    public function hasChildren()
    {
        return isset($this->elements[$this->currentIdentifier()]) && is_array($this->elements[$this->currentIdentifier()]) && ! empty($this->elements[$this->currentIdentifier()]);
    }

    public function key()
    {
        return $this->key;
    }

    public function next()
    {
        $this->key ++;
    }

    public function rewind()
    {
        $this->key = 0;
    }

    public function valid()
    {
        return isset($this->elements[$this->pointer][$this->key]);
    }

}
