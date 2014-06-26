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
 * Boilerplate stuff to disable write access operations of a view.
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class ReadonlyView implements \Nethgui\View\ViewInterface, \Nethgui\Log\LogConsumerInterface
{

    /**
     * @var \Nethgui\View\ViewInterface
     */
    protected $view;

    /**
     *
     * @var \Nethgui\Log\LogInterface
     */
    private $log;

    public function __construct(\Nethgui\View\ViewInterface $view)
    {
        if ($view instanceof ReadonlyView) {
            // Prevent re-wrapping of a read-only view instance:
            $this->view = $view->view;
        } else {
            $this->view = $view;
        }
    }

    public function copyFrom($data)
    {
        throw new \LogicException('Cannot change the view values', 1322149480);
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
        throw new \LogicException('Cannot change the view value', 1322149481);
    }

    public function offsetUnset($offset)
    {
        throw new \LogicException('Cannot unset a view value', 1322149482);
    }

    public function setTemplate($template)
    {
        throw new \LogicException('Cannot change the view template', 1322149483);
    }

    public function getTemplate()
    {
        return $this->view->getTemplate();
    }

    public function spawnView(\Nethgui\Module\ModuleInterface $module, $register = FALSE)
    {
        throw new \LogicException('Readonly view: cannot spawn another view!', 1322149484);
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

    public function setLog(\Nethgui\Log\LogInterface $log)
    {
        $this->log = $log;
        return $this;
    }

    public function getLog()
    {
        if(isset($this->log)) {
            return $this->log;
        } elseif ($this->view instanceof \Nethgui\Log\LogConsumerInterface) {
            return $this->view->getLog();
        } else {
            return new \Nethgui\Log\Nullog();
        }
    }

    public function resolvePath($path)
    {
        return $this->view->resolvePath($path);
    }

    public function getPathUrl()
    {
        return $this->view->getPathUrl();
    }

    public function getSiteUrl()
    {
        return $this->view->getSiteUrl();
    }

    public function getTargetFormat()
    {
        return $this->view->getTargetFormat();
    }

    public function getCommandList($selector = '')
    {
        return $this->view->getCommandList($selector);
    }

    public function hasCommandList($selector = '')
    {
        return $this->view->hasCommandList($selector);
    }

}
