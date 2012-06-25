<?php
namespace Nethgui\Widget\Xhtml;

/*
 * Copyright (C) 2012 Nethesis S.r.l.
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
 * Slider UI widget.
 * 
 * Select a value inside a given range
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class Slider extends \Nethgui\Widget\XhtmlWidget
{

    protected function getJsWidgetTypes()
    {
        return array_merge(array('Nethgui:inputcontrol'), parent::getJsWidgetTypes());
    }

    protected function renderContent()
    {
        $name = $this->getAttribute('name');
        $value = $this->getAttribute('value');
        $flags = $this->getAttribute('flags');
        $label = $this->getAttribute('label', $this->getTranslateClosure($name . '_label'));
        $cssClass = $this->getAttribute('class', '');
        $cssClass = trim('Slider ' . $cssClass);

        $range = array(
            'min' => $this->getAttribute('min', 0),
            'max' => $this->getAttribute('max', 100),
            'step' => $this->getAttribute('step', 1),
        );

        if (is_null($value)) {
            $value = $this->view[$name];
        }

        $attributes = array(
            'value' => strval($this->view[$name]),
            'type' => 'input',
            'data-settings' => json_encode($range),
        );

        $flags = $this->applyDefaultLabelAlignment($flags, \Nethgui\Renderer\WidgetFactoryInterface::LABEL_ABOVE);

        return $this->labeledControlTag($label, 'input', $name, $flags, $cssClass, $attributes);
    }

}