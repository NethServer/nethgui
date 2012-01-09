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
 * Attributes:
 *
 * - name, see TextLabel
 * - template, see TextLabel
 * - flags
 *
 */
class Fieldset extends Panel
{

    protected function getJsWidgetTypes()
    {
        return array('Nethgui:fieldset');
    }

    protected function renderContent()
    {
        // force container tag to FIELDSET:
        $this->setAttribute('tag', 'fieldset');
        $flags = $this->getAttribute('flags', 0);

        if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::FIELDSET_EXPANDABLE) {
            $this->setAttribute('class', 'Fieldset expandable');            
        } else {
            $this->setAttribute('class', 'Fieldset');            
        }

        $labelWidget = new TextLabel($this->view);
        $labelWidget->setAttribute('tag', 'span');
        $renderLegend = FALSE;

        if ($this->hasAttribute('name')) {
            $labelWidget->setAttribute('name', $this->getAttribute('name'));
            $renderLegend = TRUE;
        }

        if ($this->hasAttribute('template')) {
            $labelWidget->setAttribute('template', $this->getAttribute('template'));
            $renderLegend = TRUE;
        }

        if ($renderLegend && ! ($flags & \Nethgui\Renderer\WidgetFactoryInterface::LABEL_NONE)) {

            $legendWidget = $this->view->panel()->setAttribute('tag', 'legend')->insert($labelWidget);

            if ($this->hasAttribute('icon-before')) {
                $legendWidget->prepend($this->view->literal($this->openTag('span', array('class' => 'ui-icon ' . $this->getAttribute('icon-before'))) . $this->closeTag('span')));
            }

            if ($this->hasAttribute('icon-after')) {
                $legendWidget->append($this->view->literal($this->openTag('span', array('class' => 'ui-icon ' . $this->getAttribute('icon-after'))) . $this->closeTag('span')));
            }

            $this->prepend($legendWidget);
        }

        return parent::renderContent();
    }

}
