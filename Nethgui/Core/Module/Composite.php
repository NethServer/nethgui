<?php
/**
 * Nethgui
 *
 * @package Core
 * @subpackage Module
 */

/**
 * A composition of modules forwards request handling to its parts.
 * 
 * Inheriting classes must define the composition behaviour.
 * 
 *
 * @see Nethgui_Core_Module_Controller
 * @see Nethgui_Core_Module_List
 * @package Core
 * @subpackage Module
 */
abstract class Nethgui_Core_Module_Composite extends Nethgui_Core_Module_Abstract implements Nethgui_Core_ModuleCompositeInterface
{

    private $children = array();

    /**
     * Propagates initialize() message to children.
     */
    public function initialize()
    {
        parent::initialize();
        // TODO: implement child autoloading
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
     * @param Nethgui_Core_ModuleInterface $childModule
     */
    public function addChild(Nethgui_Core_ModuleInterface $childModule)
    {
        if ( ! isset($this->children[$childModule->getIdentifier()])) {
            $this->children[$childModule->getIdentifier()] = $childModule;
            $childModule->setParent($this);
            if ($this->getHostConfiguration() !== NULL) {
                $childModule->setHostConfiguration($this->getHostConfiguration());
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

    public function setHostConfiguration(Nethgui_Core_HostConfigurationInterface $hostConfiguration)
    {
        parent::setHostConfiguration($hostConfiguration);
        foreach ($this->getChildren() as $childModule) {
            $childModule->setHostConfiguration($hostConfiguration);
        }
    }

    protected function hasInputForm()
    {
        foreach ($this->getChildren() as $module) {
            if ($module instanceof Nethgui_Core_Module_Abstract
                && $module->hasInputForm()) {
                return TRUE;
            }
        }
        return FALSE;
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

            if ($item[0] == '_') {
                $childModuleClass = get_class($this) . $item;
            } else {
                $childModuleClass = $item;
            }

            $childModule = new $childModuleClass();
            if ( ! is_null($this->getHostConfiguration())) {
                $childModule->setHostConfiguration($this->getHostConfiguration());
            }

            $this->addChild($childModule);
        }
    }

    /**
     *
     * @param Nethgui_Renderer_Abstract $view
     * @param type $childId
     * @return Nethgui_Renderer_WidgetInterface
     */
    protected function wrapFormAroundChild(Nethgui_Renderer_Abstract $view, $childId, $flags = 0)
    {
        $widget = $view->inset($childId, $flags);
        if ($view[$childId] instanceof Nethgui_Core_ViewInterface) {
            $module = $view[$childId]->getModule();
            if ($module instanceof Nethgui_Core_Module_Abstract && $module->hasInputForm()) {
                // FIXME: read $flags from $view
                $renderer = new Nethgui_Renderer_Xhtml($view[$childId], $flags);
                $widget = $renderer->form()->insert($widget)->setAttribute('name', $widget->getAttribute('name'));
            }
        }
        return $widget;
    }

}

