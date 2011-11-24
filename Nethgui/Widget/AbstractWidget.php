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
 * Abstract Widget class
 */
abstract class AbstractWidget implements \Nethgui\Renderer\WidgetInterface, \Nethgui\Log\LogConsumerInterface
{

    static private $instance = 0;
    private $children = array();
    private $attributes = array();

    /**
     * @var \Nethgui\Renderer\Xhtml
     */
    protected $view;

    public function __construct(\Nethgui\Renderer\Xhtml $view)
    {
        $this->view = $view;
        self::$instance ++;
    }

    public function getAttribute($attribute, $default = NULL)
    {
        if ( ! $this->hasAttribute($attribute)) {
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

    public function render()
    {
        return $this->renderChildren();
    }

    protected function renderChildren()
    {
        $flags = $this->getAttribute('flags', 0) & NETHGUI_INHERITABLE_FLAGS;
        $output = '';
        foreach ($this->children as $child) {
            if ($child->hasAttribute('flags')) {
                $child->setAttribute('flags', $flags | $child->getAttribute('flags'));
            }
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
        } catch (Exception $ex) {
            $this->getLog()->exception($ex, TRUE);
            //throw $ex;
            return '';
        }

    }

    protected function getClientEventTarget()
    {
        if ( ! $this->hasAttribute('name')) {
            throw new \LogicException(sprintf('%s: Missing `name` attribute', get_class($this)), 1322148739);
        }

        return $this->view->getClientEventTarget($this->getAttribute('name'));
    }

    public function setLog(\Nethgui\Log\AbstractLog $log)
    {
        throw new \LogicException(sprintf('Cannot invoke setLog() on %s', get_class($this)), 1322148740);
    }

    public function getLog()
    {
        return $this->view->getLog();
    }

}
