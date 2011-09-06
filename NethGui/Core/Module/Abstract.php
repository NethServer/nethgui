<?php
/**
 * @package Core
 * @subpackage Module
 */

/**
 * @package Core
 * @subpackage Module
 */
abstract class NethGui_Core_Module_Abstract implements NethGui_Core_ModuleInterface, NethGui_Core_LanguageCatalogProvider
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
     * @var HostConfigurationInterface
     */
    private $hostConfiguration;
    
    /**
     * Template applied to view, if different from NULL
     *
     * @see NethGui_Core_ViewInterface::setTemplate()
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

    public function setHostConfiguration(NethGui_Core_HostConfigurationInterface $hostConfiguration)
    {
        $this->hostConfiguration = $hostConfiguration;
    }

    /**
     * @return NethGui_Core_HostConfigurationInterface
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

    public function setParent(NethGui_Core_ModuleInterface $parentModule)
    {
        $this->parent = $parentModule;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
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
     * @param NethGui_Renderer_Abstract $view
     * @param type $childId
     * @return NethGui_Renderer_WidgetInterface|string
     */
    protected function renderFormWrap(NethGui_Renderer_Abstract $view, $childId)
    {
        $module = $view[$childId]->getModule();
        $widget = $view->inset($childId);
        if ($module instanceof NethGui_Core_Module_Abstract) {
            if ($module->hasInputForm()) {
                $renderer = new NethGui_Renderer_Xhtml($view[$childId]);
                $widget = $renderer->form()->insert($widget)->setAttribute('name', $widget->getAttribute('name'));
            }
        }
        return $widget;
    }

}