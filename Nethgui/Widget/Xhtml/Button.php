<?php
namespace Nethgui\Widget\Xhtml;

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
 *
 * Attributes:
 * - name
 * - value
 * - flags
 * - label
 *
 */
class Button extends \Nethgui\Widget\XhtmlWidget
{

    protected function getJsWidgetTypes()
    {
        return array_merge(array('Nethgui:inputcontrol', 'Nethgui:tooltip'), parent::getJsWidgetTypes());
    }

    private function getDefaultValue($name) {
        if(! isset($this->view[$name])) {
            return NULL;
        }
        $value = $this->view[$name];
        if($value instanceof \Nethgui\View\ViewInterface) {
            return $value->getModuleUrl();
        }
        return $value;
    }

    protected function renderContent()
    {
        $name = $this->getAttribute('name');
        $flags = $this->getAttribute('flags');
        $label = $this->getAttribute('label', $this->getTranslateClosure($name . '_label'));
        $content = '';

        $attributes = array();
        $cssClass = 'Button';

        if ($this->hasAttribute('receiver')) {
            $attributes['id'] = $this->view->getUniqueId($this->getAttribute('receiver'));
        } else {
            $attributes['id'] = FALSE;
        }

        if ($flags & (\Nethgui\Renderer\WidgetFactoryInterface::BUTTON_LINK | \Nethgui\Renderer\WidgetFactoryInterface::BUTTON_CANCEL | \Nethgui\Renderer\WidgetFactoryInterface::BUTTON_HELP)) {

            $value = $this->getAttribute('value', $this->getDefaultValue($name));

            if (empty($value)) {
                $value = '';
            } elseif (is_array($value)) {
                $label = $value[1];
                $value = $value[0];
            }

            if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::BUTTON_CANCEL) {
                $cssClass .= ' cancel';
                $flags |= \Nethgui\Renderer\WidgetFactoryInterface::STATE_UNOBSTRUSIVE;
            } elseif ($flags & \Nethgui\Renderer\WidgetFactoryInterface::BUTTON_HELP) {
                if ($value === '') {
                    $value = $this->view->getModuleUrl('/Help/Read/' . \Nethgui\array_head($this->view->getModulePath()) . '.html');
                }
                $cssClass .= ' Help';
            } else {
                $cssClass .= ' link ' . $this->getClientEventTarget();
            }

            if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::STATE_DISABLED) {
                $cssClass .= ' disabled';
                $tag = 'span';
            } else {
                $attributes['href'] = $value;
                $tag = 'a';
            }

            $attributes['class'] = $cssClass;
            $attributes['title'] = $this->getAttribute('title', FALSE);

            $content .= $this->openTag($tag, $attributes);
            $content .= htmlspecialchars($label);
            $content .= $this->closeTag($tag);
        } else {
            if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::BUTTON_SUBMIT) {
                $attributes['type'] = 'submit';
                $cssClass .= ' submit';
            } elseif ($flags & \Nethgui\Renderer\WidgetFactoryInterface::BUTTON_RESET) {
                $attributes['type'] = 'reset';
                $cssClass .= ' reset';
            } elseif ($flags & \Nethgui\Renderer\WidgetFactoryInterface::BUTTON_CUSTOM) {
                $attributes['type'] = 'button';
                $cssClass .= ' custom';
            } elseif ($flags & \Nethgui\Renderer\WidgetFactoryInterface::BUTTON_DROPDOWN) {
                $attributes['type'] = 'button';
                $attributes['name'] = FALSE;
                $cssClass .= ' dropdown';
                $childContent = $this->renderChildren();
            }

            $attributes['value'] = $label;

            $content .= $this->controlTag('button', $name, $flags, $this->getAttribute('class', $cssClass), $attributes);
            if (isset($childContent)) {
                $content .= $childContent;
            }
        }

        if($this->canEscapeUnobstrusive($flags)) {
            return $this->escapeUnobstrusive($content);
        }
        return $content;
    }

    public function executeCommand(\Nethgui\View\ViewInterface $origin, $selector, $name, $arguments)
    {
        $this->getLog()->deprecated(sprintf("%%s %%s: %s() command is DEPRECATED on Xhtml widget!", __CLASS__, $name));
        switch ($name) {
            case 'setLabel':
                $this->setAttribute('label', $arguments[0]);
                break;
            default:
                parent::executeCommand($origin, $selector, $name, $arguments);
        }
    }

}

