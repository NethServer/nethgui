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

            if ($submodule == $module->getIdentifier()) {
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

    public function process()
    {
        foreach ($this->getChildren() as $childModule) {
            $childModule->process();
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
        // Only a root module emits FORM tag:
        if (is_null($this->getParent())) {
            $form = $view->form();
        } else {
            $form = $view;
        }

        foreach ($this->getChildren() as $child) {
            $form->inset($child->getIdentifier());
        }

        $form->includeTemplate(array($this, 'renderButtons'));

        return $view;
    }

    public function renderButtons(NethGui_Renderer_Abstract $view)
    {
        $submitButton = (string) $view->button('Submit', NethGui_Renderer_Abstract::BUTTON_SUBMIT);
        $resetButton = (string) $view->button('Reset', NethGui_Renderer_Abstract::BUTTON_RESET);
        return "<ul class=\"actions\"><li>${submitButton}</li><li>${resetButton}</li></ul>";
    }

    public function renderTabs(NethGui_Renderer_Abstract $view)
    {
        $pages = array();

        foreach ($this->getChildren() as $child) {
            $pages[] = $child->getIdentifier();
        }

        // Only a root module emits FORM tag:
        if (is_null($this->getParent())) {
            $form = $view->form();
        } else {
            $form = $view;
        }

        $tabs = $form->tabs($this->getIdentifier(), $pages);

        $tabs->includeTemplate(array($this, 'renderButtons'));


        return $view;
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

}