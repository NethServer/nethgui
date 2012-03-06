<?php
namespace Nethgui\Module;

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
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 * @see Controller
 * @see List
 */
abstract class Composite extends \Nethgui\Module\AbstractModule implements \Nethgui\Module\ModuleCompositeInterface
{
    private $children = array();

    /**
     *
     * @var ModuleLoader
     */
    private $childLoader;

    /**
     * Propagates initialize() message to children.
     *
     * @api
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
     * @api
     * @param \Nethgui\Module\ModuleInterface $childModule
     * @return Composite
     */
    public function addChild(\Nethgui\Module\ModuleInterface $childModule)
    {
        if (isset($this->children[$childModule->getIdentifier()])) {
            throw new \LogicException(sprintf('%s: the module identifier "%s" is already registered as child!', __CLASS__, $childModule->getIdentifier()), 1322818691);
        }

        $this->children[$childModule->getIdentifier()] = $childModule;

        $childModule->setParent($this);
        if ($this->hasPlatform() && $childModule instanceof \Nethgui\System\PlatformConsumerInterface) {
            $childModule->setPlatform($this->getPlatform());
        }

        if ($this->isInitialized() && ! $childModule->isInitialized()) {
            $childModule->initialize();
        }       
        return $this;
    }

    /**
     * Get the parts of this Composite.
     *
     * @api
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
        return $this;
    }

    /**
     * Instantiates the given classes, adding the created objects as children of
     * the composite module.
     *
     * If the first character is `*` (asterisk), the container class name
     * is prepended.
     *
     * @see addChild()
     * @link http://redmine.nethesis.it/issues/196
     * @param type $classList
     * @return \Nethgui\Module\Composite
     */
    protected function loadChildren($classList)
    {
        foreach ($classList as $item) {
            if ( ! is_string($item)) {
                throw new \InvalidArgumentException(sprintf('%s: $classList elements must be of type String', get_class($this)), 1322148900);
            }

            if (substr($item, 0, 2) === '*\\') {
                $childModuleClass = get_class($this) . '\\' . substr($item, 2);
            } else {
                $childModuleClass = $item;
            }

            $childModule = new $childModuleClass();
            $this->addChild($childModule);
        }
    }

    /**
     *
     * @param string $path
     * @return \Nethgui\Module\Composite 
     */
    protected function loadChildrenDirectory()
    {
        if ( ! isset($this->childLoader)) {
            $this->childLoader = new \Nethgui\Module\ModuleLoader();
            $this->childLoader
                ->setLog($this->getLog())
                ->setNamespaceFromModule($this);                                   
        }
        foreach ($this->childLoader as  $childInstance) {
            $this->addChild($childInstance);
        }
        return $this;
    }

    /**
     * Sort children applying the given callback function
     *
     * @see http://php.net/manual/en/function.usort.php
     * @param callable $callback
     * @return void;
     */
    protected function sortChildren($callback)
    {
        if ( ! is_callable($callback)) {
            throw new \InvalidArgumentException(sprintf('%s: invalid callback for %s#%s()', get_class($this), __CLASS__, __FUNCTION__), 1325760755);
        }

        usort($this->children, $callback);
    }

    public function setLog(\Nethgui\Log\LogInterface $log)
    {
        if (isset($this->childLoader)) {
            $this->childLoader->setLog($log);
        }
        return parent::setLog($log);
    }

}
