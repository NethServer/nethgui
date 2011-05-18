<?php
/**
 * NethGui
 *
 * @package Core
 * @subpackage Module
 */

/**
 * A List of modules that forwards request handling to its parts.
 * 
 * A List executes no action. It forwards each call to its subparts. 
 * 
 * You can instruct a List to render a plain list, a form or tabs container.
 *
 * @see NethGui_Core_Module_Composite
 * @package Core
 * @subpackage Module
 */
class NethGui_Core_Module_List extends NethGui_Core_Module_Composite implements NethGui_Core_RequestHandlerInterface
{
    const TEMPLATE_LIST = 1;
    const TEMPLATE_FORM = 2;
    const TEMPLATE_TABS = 3;

    public function __construct($identifier = NULL, $template = self::TEMPLATE_LIST)
    {
        parent::__construct($identifier);
        if ($template === self::TEMPLATE_FORM) {
            $this->viewTemplate = array($this, 'renderForm');
        } elseif ($template === self::TEMPLATE_TABS) {
            $this->viewTemplate = array($this, 'renderTabs');
        } elseif ($template === self::TEMPLATE_LIST) {
            $this->viewTemplate = array($this, 'renderList');
        } else {
            $this->viewTemplate = $template;
        }
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