<?php
namespace Nethgui\Core;

/*
 * Copyright (C) 2011 Nethesis S.r.l.
 * 
 * This script is part of NethServer.
 * 
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @todo Describe class...
 * @internal
 */
final class ModuleMenuIterator implements \RecursiveIterator
{

    private $elements;
    /**
     * @var ModuleSetInterface
     */
    private $moduleSet;
    private $pointer;
    private $key;


    public function __construct(ModuleSetInterface $moduleSet, $pointer, &$elements = array())
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
