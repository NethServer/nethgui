<?php
namespace Nethgui\Core\Module;

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
 * A composition of modules forwards request handling to its parts.
 * 
 * Inheriting classes must define the composition behaviour.
 * 
 *
 * @see Controller
 * @see List
 */
abstract class Composite extends AbstractModule implements \Nethgui\Core\ModuleCompositeInterface
{

    private $children = array();

    /**
     * Propagates initialize() message to children.
     * 
     * @see loadChildren()
     */
    public function initialize()
    {
        parent::initialize();
        foreach ($this->children as $child) {
            if ( ! $child->isInitialized()) {
                $child->initialize();
            }
        }
    }

    /**
     * Adds a child to Composite, initializing it, if current Composite is
     * initialized.
     * 
     * @param \Nethgui\Core\ModuleInterface $childModule
     */
    public function addChild(\Nethgui\Core\ModuleInterface $childModule)
    {
        if ( ! isset($this->children[$childModule->getIdentifier()])) {
            $this->children[$childModule->getIdentifier()] = $childModule;
            $childModule->setParent($this);
            if ($this->getPlatform() !== NULL) {
                $childModule->setPlatform($this->getPlatform());
            }
            if ($this->isInitialized() && ! $childModule->isInitialized()) {
                $childModule->initialize();
            }
        }
    }

    /**
     * Get the parts of this Composite.
     *
     * @return array
     */
    public function getChildren()
    {
        // TODO: authorize access request on policy decision point.
        return array_values($this->children);
    }

    public function setPlatform(\Nethgui\System\PlatformInterface $platform)
    {
        parent::setPlatform($platform);
        foreach ($this->getChildren() as $childModule) {
            $childModule->setPlatform($platform);
        }
    }

    /**
     * Instantiates the given classes, adding the created objects as children of
     * this List module.
     *
     * If the class name begins with `_` (underscore), the container class name
     * is prepended.
     *
     * @see addChild()
     * @link http://redmine.nethesis.it/issues/196
     * @param type $classList
     * @return void
     */
    protected function loadChildren($classList)
    {
        foreach ($classList as $item) {
            if ( ! is_string($item)) {
                throw new \InvalidArgumentException(sprintf('%s: $classList elements must be of type String', get_class($this)), 1322148900);
            }

            if ($item[0] == '\\') {
                $childModuleClass = $item;
            } else {
                $childModuleClass = get_class($this) . '\\' . $item;
            }

            $childModule = new $childModuleClass();
            $this->addChild($childModule);
        }
    }

    protected function loadChildrenFromPath($path)
    {
        throw new \LogicException(sprintf('%s: %s() is not Implemented', get_class($this),  __FUNCTION__), 1322148901);
    }



}

