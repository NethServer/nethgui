<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class NethGui_Core_ReadonlyView implements NethGui_Core_ViewInterface
{
    /**
     *
     * @var NethGui_Core_ViewInterface
     */
    protected $view;

    public function __construct(NethGui_Core_ViewInterface $view)
    {
        $this->view = $view;
    }

    public function copyFrom($data)
    {
        throw new NethGui_Exception_View('Cannot change the view values');
    }

    public function getIterator()
    {
        return $this->view->getIterator();
    }

    public function getModule()
    {
        return $this->view->getModule();
    }

    public function offsetExists($offset)
    {
        return $this->view->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->view->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        throw new NethGui_Exception_View('Cannot change the view value');
    }

    public function offsetUnset($offset)
    {
        throw new NethGui_Exception_View('Cannot unset a view value');
    }

    public function setTemplate($template)
    {
        throw new NethGui_Exception_View('Cannot change the view template');
    }

    public function getTemplate()
    {
        return $this->view->getTemplate();
    }

    public function spawnView(NethGui_Core_ModuleInterface $module, $register = FALSE)
    {
        throw new NethGui_Exception_View('Cannot spawn a view now');
    }

    public function translate($message, $args = array())
    {
        return $this->view->translate($message, $args);
    }

    public function getModulePath()
    {
        return $this->view->getModulePath();
    }

    public function getUniqueId($parts = NULL)
    {
        return $this->view->getUniqueId($parts);
    }

    public function getControlName($parts = '')
    {
        return $this->view->getControlName($parts);
    }

    public function render()
    {
        return $this->view->render();
    }

}
