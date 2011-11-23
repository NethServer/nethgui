<?php
/**
 * Nethgui
 *
 * @package Core
 * @subpackage Module
 */

namespace Nethgui\Core\Module;

/**
 * A List of modules that forwards request handling to its parts.
 * 
 * A List executes no action. It forwards each call to its subparts. 
 *
 * @see Composite
 * @package Core
 * @subpackage Module
 */
class List extends Composite implements \Nethgui\Core\RequestHandlerInterface
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

    public function bind(\Nethgui\Core\RequestInterface $request)
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

    public function validate(\Nethgui\Core\ValidationReportInterface $report)
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

    public function prepareView(\Nethgui\Core\ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        foreach ($this->getChildren() as $child) {
            $innerView = $view->spawnView($child, TRUE);
            $child->prepareView($innerView, $mode);
        }
    }

    public function renderList(\Nethgui\Renderer\Abstract $view)
    {
        $widget = $view->panel();
        foreach ($this->getChildren() as $child) {
            $widget->insert($view->inset($child->getIdentifier()));
        }
        $widget->setAttribute('class', 'List');
        return $widget;
    }

}
