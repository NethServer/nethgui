<?php
namespace Nethgui\Renderer;

/*
 * Copyright (C) 2011 Nethesis S.r.l.
 * 
 * This script is part of NethServer.
 * 
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Disable write access operations of a view.
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class ReadonlyView implements \Nethgui\Core\ViewInterface, \Nethgui\Log\LogConsumerInterface
{

    /**
     * @var \Nethgui\Core\ViewInterface
     */
    protected $view;

    public function __construct(\Nethgui\Core\ViewInterface $view)
    {
        if ($view instanceof self) {
            // Prevent re-wrapping of a read-only view instance:
            $this->view = $view->view;
        } else {
            $this->view = $view;
        }
    }

    public function copyFrom($data)
    {
        throw new \Nethgui\Exception\View('Cannot change the view values');
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
        throw new \Nethgui\Exception\View('Cannot change the view value');
    }

    public function offsetUnset($offset)
    {
        throw new \Nethgui\Exception\View('Cannot unset a view value');
    }

    public function setTemplate($template)
    {
        throw new \Nethgui\Exception\View('Cannot change the view template');
    }

    public function getTemplate()
    {
        return $this->view->getTemplate();
    }

    public function spawnView(\Nethgui\Core\ModuleInterface $module, $register = FALSE)
    {
        throw new \Nethgui\Exception\View('Readonly view: cannot spawn another view!');
    }

    public function translate($message, $args = array())
    {
        return $this->view->translate($message, $args);
    }

    public function getTranslator()
    {
        return $this->view->getTranslator();
    }

    public function getModulePath()
    {
        return $this->view->getModulePath();
    }

    public function getUniqueId($parts = '')
    {
        return $this->view->getUniqueId($parts);
    }

    public function getClientEventTarget($name)
    {
        return $this->view->getClientEventTarget($name);
    }

    public function getModuleUrl($path = '')
    {
        return $this->view->getModuleUrl($path);
    }

    public function setLog(\Nethgui\Log\AbstractLog $log)
    {
        throw new Exception(sprintf('Cannot invoke setLog() on %s', get_class($this)));
    }

    public function getLog()
    {
        if ($this->view instanceof \Nethgui\Log\LogConsumerInterface) {
            return $this->view->getLog();
        } else {
            return new \Nethgui\Log\Nullog();
        }
    }

    public function createUiCommand($methodName, $arguments)
    {
        return $this->view->createUiCommand($methodName, $arguments);
    }
    
    public function resolvePath($path)
    {
        return $this->view->resolvePath($path);
    }
}
