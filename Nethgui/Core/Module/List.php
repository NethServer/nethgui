<?php
/**
 * Nethgui
 *
 * @package Core
 * @subpackage Module
 */

/**
 * A List of modules that forwards request handling to its parts.
 * 
 * A List executes no action. It forwards each call to its subparts. 
 *
 * @see Nethgui_Core_Module_Composite
 * @package Core
 * @subpackage Module
 */
class Nethgui_Core_Module_List extends Nethgui_Core_Module_Composite implements Nethgui_Core_RequestHandlerInterface
{
    const TEMPLATE_LIST = 1;

    public function __construct($identifier = NULL, $template = self::TEMPLATE_LIST)
    {
        parent::__construct($identifier);
        if ($template === self::TEMPLATE_LIST) {
            $this->setViewTemplate(array($this, 'renderList'));
        } else {
            $this->setViewTemplate($template);
        }
    }

    public function bind(Nethgui_Core_RequestInterface $request)
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

    public function validate(Nethgui_Core_ValidationReportInterface $report)
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

    public function prepareView(Nethgui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        foreach ($this->getChildren() as $child) {
            $innerView = $view->spawnView($child, TRUE);
            $child->prepareView($innerView, $mode);
        }
    }

    public function renderList(Nethgui_Renderer_Abstract $view)
    {
        $widget = $view->panel();
        foreach ($this->getChildren() as $child) {
            $widget->insert($view->inset($child->getIdentifier()));
        }
        $widget->setAttribute('class', 'List');
        return $widget;
    }

}