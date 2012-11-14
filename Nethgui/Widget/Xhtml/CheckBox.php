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
 */
class CheckBox extends \Nethgui\Widget\XhtmlWidget
{

    protected function getJsWidgetTypes()
    {
        return array('Nethgui:inputcontrol', 'Nethgui:tooltip');
    }

    protected function renderContent()
    {
        $name = $this->getAttribute('name');
        $value = $this->getAttribute('value');
        $uncheckedValue = $this->getAttribute('uncheckedValue', '');
        $flags = $this->getAttribute('flags');
        $label = $this->getAttribute('label', $this->getTranslateClosure($name . '_label'));
        $content = '';

        $flags = $this->applyDefaultLabelAlignment($flags, \Nethgui\Renderer\WidgetFactoryInterface::LABEL_RIGHT);

        $attributes = array(
            'type' => 'checkbox',
            'value' => $value,
        );

        if ($value === $this->view[$name] || $flags & \Nethgui\Renderer\WidgetFactoryInterface::STATE_CHECKED) {
            $attributes['checked'] = 'checked';
        }

        if ($uncheckedValue !== FALSE) {
            $content .= $this->controlTag('input', $name, $flags, 'HiddenConst', array('type' => 'hidden', 'value' => $uncheckedValue, 'id' => FALSE));
            if ($this->view[$name] === $uncheckedValue) {
                $attributes['checked'] = FALSE;
            }
        }

        $content .= $this->labeledControlTag($label, 'input', $name, $flags, 'CheckBox', $attributes);

        return $content;
    }

}
