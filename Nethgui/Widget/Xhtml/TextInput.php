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
class TextInput extends \Nethgui\Widget\XhtmlWidget
{

    protected function getJsWidgetTypes()
    {
        return array_merge(array('Nethgui:inputcontrol'), parent::getJsWidgetTypes());
    }

    protected function renderContent()
    {
        $name = $this->getAttribute('name');
        $flags = $this->getAttribute('flags');
        $label = $this->getAttribute('label', $this->getTranslateClosure($name . '_label'));

        $defaultMandatory = $this->getAttribute('mandatory',
            isset($this->view['__mandatoryFields']) 
            && $this->view['__mandatoryFields'][$this->view->getUniqueId($name)]);
        
        $cssClass = trim(sprintf('TextInput %s %s',
                $this->getAttribute('class', ''),
                $defaultMandatory === TRUE ? 'mandatory' : ''
        ));
        $content = '';

        $attributes = array(
            'value' => strval($this->view[$name]),
            'type' => ($flags & \Nethgui\Renderer\WidgetFactoryInterface::TEXTINPUT_PASSWORD) ? 'password' : 'text',
            'placeholder' => $this->getAttribute('placeholder',false),
        );

        $flags = $this->applyDefaultLabelAlignment($flags, \Nethgui\Renderer\WidgetFactoryInterface::LABEL_ABOVE);

        // Check if $name is in the list of invalid parameters.
        if (isset($this->view['__invalidParameters']) && in_array($name, $this->view['__invalidParameters'])) {
            $flags |= \Nethgui\Renderer\WidgetFactoryInterface::STATE_VALIDATION_ERROR;
        }

        $content .= $this->labeledControlTag($label, 'input', $name, $flags, $cssClass, $attributes);

        return $content;
    }

}