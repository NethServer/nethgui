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

    public function hasInputForm() {
        return FALSE;
    }

}