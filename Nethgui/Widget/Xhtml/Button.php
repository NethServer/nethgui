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

    protected function renderContent()
    {
        $name = $this->getAttribute('name');
        $flags = $this->getAttribute('flags');
        $label = $this->getAttribute('label', $this->getTranslateClosure($name . '_label'));
        $content = '';

        $attributes = array();
        $cssClass = 'Button';

        if ($flags & (\Nethgui\Renderer\WidgetFactoryInterface::BUTTON_LINK | \Nethgui\Renderer\WidgetFactoryInterface::BUTTON_CANCEL | \Nethgui\Renderer\WidgetFactoryInterface::BUTTON_HELP)) {

            $value = $this->getAttribute('value', isset($this->view[$name]) ? $this->view[$name] : NULL);

            if (is_null($value)) {
                if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::BUTTON_LINK) {
                    $value = $name;
                } elseif ($flags & \Nethgui\Renderer\WidgetFactoryInterface::BUTTON_CANCEL) {
                    $value = '..';
                } elseif ($flags & \Nethgui\Renderer\WidgetFactoryInterface::BUTTON_HELP) {
                    $value = \Nethgui\array_head($this->view->getModulePath());
                }
            }

            if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::BUTTON_CANCEL) {
                $cssClass .= ' cancel';
            } elseif ($flags & \Nethgui\Renderer\WidgetFactoryInterface::BUTTON_HELP) {
                $value = '/Help/Read/' . urlencode($value) . '.html#HelpArea';
                $cssClass .= ' Help';
            } else {
                $cssClass .= ' link ' . $this->getClientEventTarget();
            }

            $attributes['href'] = $this->prepareHrefAttribute($value);
            $attributes['class'] = $this->appendReceiverName($cssClass);
            $attributes['title'] = $this->getAttribute('title', FALSE);

            $content .= $this->openTag('a', $attributes);
            $content .= htmlspecialchars($label);
            $content .= $this->closeTag('a');
        } else {
            if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::BUTTON_SUBMIT) {
                $attributes['type'] = 'submit';
                $cssClass .= ' submit';
                $attributes['id'] = FALSE;
                $attributes['name'] = FALSE;
            } elseif ($flags & \Nethgui\Renderer\WidgetFactoryInterface::BUTTON_RESET) {
                $attributes['type'] = 'reset';
                $cssClass .= ' reset';
            } elseif ($flags & \Nethgui\Renderer\WidgetFactoryInterface::BUTTON_CUSTOM) {
                $attributes['type'] = 'button';
                $cssClass .= ' custom';
            } elseif ($flags & \Nethgui\Renderer\WidgetFactoryInterface::BUTTON_DROPDOWN) {
                $attributes['type'] = 'button';
                $attributes['name'] = FALSE;
                $attributes['id'] = FALSE;
                $cssClass .= ' dropdown';
                $childContent = $this->renderChildren();
            }

            $attributes['value'] = $label;

            $content .= $this->controlTag('button', $name, $flags, $this->appendReceiverName($cssClass), $attributes);
            if (isset($childContent)) {
                $content .= $childContent;
            }
        }

        return $content;
    }

    private function prepareHrefAttribute($value)
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException(sprintf('%s: $value argument must be a string', get_class($this)), 1324051523);
        }

        return $value;
    }

    public function executeCommand(\Nethgui\Core\ViewInterface $origin, $selector, $name, $arguments)
    {
        switch ($name) {
            case 'setLabel':
                $this->setAttribute('label', $arguments[0]);
                break;
            default:
                parent::executeCommand($origin, $selector, $name, $arguments);
        }
    }

}

