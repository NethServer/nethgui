<?php
/**
 * @package Core
 * @subpackage Module
 */

/**
 * @package Core
 * @subpackage Module
 */
abstract class Nethgui_Core_Module_Abstract implements Nethgui_Core_ModuleInterface, Nethgui_Core_LanguageCatalogProvider
{

    /**
     * @var string
     */
    private $identifier;
    /**
     *
     * @var ModuleInterface;
     */
    private $parent;
    /*
     * @var bool
     */
    private $initialized = FALSE;
    /**
     * @var Nethgui_Core_HostConfigurationInterface
     */
    private $hostConfiguration;
    
    /**
     * Template applied to view, if different from NULL
     *
     * @see Nethgui_Core_ViewInterface::setTemplate()
     * @var string|callable
     */
    private $viewTemplate;

    public function __construct($identifier = NULL)
    {
        $this->viewTemplate = NULL;
        if (isset($identifier)) {
            $this->identifier = $identifier;
        } else {
            $this->identifier = array_pop(explode('_', get_class($this)));
        }
    }

    public function setHostConfiguration(Nethgui_Core_HostConfigurationInterface $hostConfiguration)
    {
        $this->hostConfiguration = $hostConfiguration;
    }

    /**
     * @return Nethgui_Core_HostConfigurationInterface
     */
    protected function getHostConfiguration()
    {
        return $this->hostConfiguration;
    }

    /**
     *  Overriding methods can read current state from model.
     */
    public function initialize()
    {
        if ($this->initialized === FALSE) {
            $this->initialized = TRUE;
        } else {
            throw new Exception("Double Module initialization is forbidden.");
        }
    }

    public function isInitialized()
    {
        return $this->initialized;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getTitle()
    {
        return array_pop(explode('_', $this->getIdentifier())) . '_Title';
    }

    public function getDescription()
    {
        return $this->getTitle() . '_Description';
    }

    public function setParent(Nethgui_Core_ModuleInterface $parentModule)
    {
        $this->parent = $parentModule;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function prepareView(Nethgui_Core_ViewInterface $view, $mode)
    {
        $template = $this->getViewTemplate();
        if (!is_null($template)) {
            $view->setTemplate($template);
        }
    }

    protected function setViewTemplate($template) {
        $this->viewTemplate = $template;
    }

    protected function getViewTemplate() {
        return $this->viewTemplate;
    }
    
    /**
     * @param string $languageCode
     * @return string
     */
    public function getLanguageCatalog()
    {
        return get_class($this);
    }

    /**
     * Return TRUE from this method if
     * @return boolean
     */
    protected function hasInputForm() {
        return FALSE;
    }

    /**
     *
     * @param Nethgui_Renderer_Abstract $view
     * @param type $childId
     * @return Nethgui_Renderer_WidgetInterface
     */
    protected function wrapFormAroundChild(Nethgui_Renderer_Abstract $view, $childId, $flags = 0)
    {
        $module = $view[$childId]->getModule();
        $widget = $view->inset($childId, $flags);
        if ($module instanceof Nethgui_Core_Module_Abstract) {
            if ($module->hasInputForm()) {
                // FIXME: read $flags from $view
                $renderer = new Nethgui_Renderer_Xhtml($view[$childId], $flags);
                $widget = $renderer->form()->insert($widget)->setAttribute('name', $widget->getAttribute('name'));
            }
        }
        return $widget;
    }

}