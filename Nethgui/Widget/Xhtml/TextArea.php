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
 * This is a multi-line text input field.
 *
 * A _console_ widget is a readonly text area where strings can only be appended
 * (see appendOnly attribute)
 *
 * Attributes:
 * - dimensions
 * - appendOnly
 * - data
 *
 */
class TextArea extends \Nethgui\Widget\XhtmlWidget
{

    protected function getJsWidgetTypes()
    {
        return array('Nethgui:inputcontrol', 'Nethgui:tooltip', 'Nethgui:textarea');
    }

    protected function renderContent()
    {
        $name = $this->getAttribute('name');
        $flags = $this->getAttribute('flags', 0);        
        $dimensions = explode('x', $this->getAttribute('dimensions', '20x30'));
        $rows = intval($dimensions[0]);
        $cols = intval($dimensions[1]);
        $label = $this->getAttribute('label', $this->getTranslateClosure($name . '_label'));
        $value = htmlspecialchars($this->view[$name]);

        $tagContent = '';
        $htmlAttributes = array(
            'rows' => $rows,
            'cols' => $cols,
            'placeholder' => $this->getAttribute('placeholder', FALSE)
        );

        $cssClass = trim('TextArea ' . $this->getAttribute('class', ''));

        if ($this->getAttribute('appendOnly', FALSE)) {
            $cssClass .= ' appendOnly';
        }

        return $this->labeledControlTag($label, 'textarea', $name, $flags, $cssClass, $htmlAttributes, $value);
    }

}
