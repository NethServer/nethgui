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
class Hidden extends \Nethgui\Widget\XhtmlWidget
{

    protected function getJsWidgetTypes()
    {
        return array('Nethgui:inputcontrol', 'Nethgui:tooltip');
    }

    protected function renderContent()
    {
        $name = $this->getAttribute('name');
        $value = $this->getAttribute('value', $this->view[$name]);
        $flags = $this->getAttribute('flags');
        $content = '';

        if ( ! is_array($value)) {
            $value = array($name => $value);
        }

        $content .= $this->hiddenArrayRecursive($value, $flags);

        return $content;
    }

    private function hiddenArrayRecursive($valueArray, $flags, $path = array())
    {
        $content = '';

        foreach ($valueArray as $name => $value) {
            $namePath = $path;
            $namePath[] = $name;

            if (is_array($value)) {
                $content .= $this->hiddenArrayRecursive($value, $flags, $namePath);
            } else {
                $attributes = array(
                    'type' => 'hidden',
                    'value' => $value,
                    'name' => $this->getControlName(implode('/', $namePath)),
                    'id' => FALSE
                );

                if ($this->hasAttribute('class')) {
                    $attributes['class'] = $this->getAttribute('class');
                }

                $content .= $this->controlTag('input', FALSE, $flags, 'Hidden', $attributes);
            }
        }

        return $content;
    }

}
