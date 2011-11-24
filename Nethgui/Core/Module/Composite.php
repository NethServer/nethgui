<?php
/**
 * Nethgui
 *
 */

namespace Nethgui\Core\Module;

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
                throw new InvalidArgumentException('$classList elements must be of type String');
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
        throw new Exception(sprintf('%s() is not Implemented'), __FUNCTION__);
    }



}

