<?php
/**
 * NethGui
 *
 * @package Core
 * @subpackage Module
 */

/**
 * A composition of modules that forwards request handling to its parts.
 * 
 * A Composite executes no action. It forwards each call to its subparts. 
 * 
 * You can instruct a Composite to render a plain list, a form or tabs container.
 *
 * @see NethGui_Core_Module_Controller
 * @package Core
 * @subpackage Module
 */
class NethGui_Core_Module_Composite extends NethGui_Core_Module_Abstract implements NethGui_Core_ModuleCompositeInterface, NethGui_Core_RequestHandlerInterface
{

    private $children = array();

    const TEMPLATE_LIST = 0;
    const TEMPLATE_FORM = 1;
    const TEMPLATE_TABS = 2;

    public function __construct($identifier = NULL, $template = self::TEMPLATE_LIST)
    {
        parent::__construct($identifier);
        if ($template == self::TEMPLATE_FORM) {
            $this->viewTemplate = array($this, 'renderForm');
        } elseif ($template == self::TEMPLATE_TABS) {
            $this->viewTemplate = array($this, 'renderTabs');
        } else {
            $this->viewTemplate = array($this, 'renderList');
        }
    }

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
     * @param NethGui_Core_ModuleInterface $childModule
     */
    public function addChild(NethGui_Core_ModuleInterface $childModule)
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

    public function bind(NethGui_Core_RequestInterface $request)
    {
        $arguments = $request->getArguments();        
        $submodule = array_shift($arguments);        
        foreach ($this->getChildren() as $module) {
            
            if($submodule == $module->getIdentifier()) {
                // Forward arguments to submodule:
                $module->bind($request->getParameterAsInnerRequest($submodule, $arguments));
            } else {
                $module->bind($request->getParameterAsInnerRequest($module->getIdentifier()));                
            }
            
        }
    }

    public function validate(NethGui_Core_ValidationReportInterface $report)
    {
        foreach ($this->getChildren() as $module) {
            $module->validate($report);
        }
    }

    public function process(NethGui_Core_NotificationCarrierInterface $carrier)
    {
        foreach ($this->getChildren() as $childModule) {
            $childModule->process($carrier);
        }
    }

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        foreach ($this->getChildren() as $childModule) {
            $innerView = $view->spawnView($childModule, TRUE);
            $childModule->prepareView($innerView, $mode);
        }
    }

    public function setHostConfiguration(NethGui_Core_HostConfigurationInterface $hostConfiguration)
    {
        parent::setHostConfiguration($hostConfiguration);
        foreach ($this->getChildren() as $childModule) {
            $childModule->setHostConfiguration($hostConfiguration);
        }
    }

    public function renderList(NethGui_Renderer_Abstract $view)
    {
        foreach ($this->getChildren() as $child) {
            $view->inset($child->getIdentifier());
        }
        return $view;
    }

    public function renderForm(NethGui_Renderer_Abstract $view)
    {
        $form = $view->form();

        foreach ($this->getChildren() as $child) {
            $form->inset($child->getIdentifier());
        }

        $form->button('Submit', NethGui_Renderer_Abstract::BUTTON_SUBMIT);
        $form->button('Reset', NethGui_Renderer_Abstract::BUTTON_RESET);

        return $view;
    }

    public function renderTabs(NethGui_Renderer_Abstract $view)
    {
        $pages = array();

        foreach ($this->getChildren() as $child) {
            $pages[] = $child->getIdentifier();
        }

        $tabs = $view->form()->tabs($this->getIdentifier(), $pages);

        $tabs->button('Submit', NethGui_Renderer_Abstract::BUTTON_SUBMIT);
        $tabs->button('Reset', NethGui_Renderer_Abstract::BUTTON_RESET);

        return $view;
    }

}

