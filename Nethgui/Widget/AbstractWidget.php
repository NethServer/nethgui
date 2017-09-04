<?php
namespace Nethgui\Widget;

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
 * This abstract widget implementation collaborates with a ViewInterface object. 
 * 
 * In this collaboration the Widget plays the "view" role and the ViewInterface object
 * the "model" role, where data is stored.  
 * 
 * Concrete implementations of an abstract widget specify how the data of the 
 * model is rendered into a string.
 * 
 * @api
 * @since 1.0
 * @author Davide Principi <davide.principi@nethesis.it>
 */
abstract class AbstractWidget implements \Nethgui\Renderer\WidgetInterface, \Nethgui\Log\LogConsumerInterface
{
    static private $instance = 0;
    private $children = array();
    private $attributes = array();
    private $log;

    /**
     * @var \Nethgui\View\ViewInterface
     */
    protected $view;

    public function __construct(\Nethgui\View\ViewInterface $view)
    {
        $this->view = $view;
        self::$instance ++;
    }

    public function getAttribute($attribute, $default = NULL)
    {
        if ( ! $this->hasAttribute($attribute)) {
            if (is_callable($default)) {
                return call_user_func($default, $attribute);
            }
            return $default;
        }
        return $this->attributes[$attribute];
    }

    public function setAttribute($attribute, $value)
    {
        $this->attributes[$attribute] = $value;
        return $this;
    }

    public function hasAttribute($attribute)
    {
        return array_key_exists($attribute, $this->attributes);
    }

    protected static function getInstanceCounter()
    {
        return self::$instance;
    }

    protected function prepend(\Nethgui\Renderer\WidgetInterface $widget)
    {
        array_unshift($this->children, $widget);
        return $this;
    }

    public function insert(\Nethgui\Renderer\WidgetInterface $widget)
    {
        $this->children[] = $widget;
        return $this;
    }

    protected function renderContent()
    {
        return $this->renderChildren();
    }

    protected function renderChildren()
    {
        $output = '';
        foreach ($this->children as $child) {
            $output .= $this->wrapChild($child->render());
        }
        return $output;
    }

    protected function getChildren()
    {
        return $this->children;
    }

    protected function hasChildren()
    {
        return ! empty($this->children);
    }

    protected function wrapChild($childOutput)
    {
        return $childOutput;
    }

    public function __toString()
    {
        try {
            return $this->render();
        } catch (\Exception $ex) {
            $this->getLog()->exception($ex, TRUE);
            return '';
        }
    }

    public function render()
    {
        return $this->renderContent();
    }

    protected function getClientEventTarget()
    {
        if ( ! $this->hasAttribute('name')) {
            throw new \LogicException(sprintf('%s: Missing `name` attribute', get_class($this)), 1322148739);
        }

        return $this->view->getClientEventTarget($this->getAttribute('name'));
    }

    public function setLog(\Nethgui\Log\LogInterface $log)
    {
        $this->log = $log;
        return $this;
    }

    public function getLog()
    {
        if( ! isset($this->log)) {
            $this->log = new \Nethgui\Log\Nullog();
        }
        return $this->log;
    }

    /**
     * Insert any (sub)view object found in the view $name collection
     * into the widget.
     * 
     * @see insert()
     * @api
     * @param string $name Optional - Default "Plugin"
     * @return \Nethgui\Widget\AbstractWidget the widget itself.
     */
    abstract public function insertPlugins($name = 'Plugin');
}
