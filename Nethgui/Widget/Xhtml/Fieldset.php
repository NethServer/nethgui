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

    public function render()
    {
        // force container tag to FIELDSET:
        $this->setAttribute('tag', 'fieldset');
        $flags = $this->getAttribute('flags', 0);

        if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::FIELDSET_EXPANDABLE) {
            $this->setAttribute('class', 'Fieldset expandable');
        } else {
            $this->setAttribute('class', 'Fieldset');
        }

        $legendWidget = new TextLabel($this->view);
        $legendWidget->setAttribute('tag', 'legend');
        $renderLegend = FALSE;

        if ($this->hasAttribute('name')) {
            $legendWidget->setAttribute('name', $this->getAttribute('name'));
            $renderLegend = TRUE;
        }

        if ($this->hasAttribute('template')) {
            $legendWidget->setAttribute('template', $this->getAttribute('template'));
            $renderLegend = TRUE;
        }

        if ($renderLegend && ! ($flags & \Nethgui\Renderer\WidgetFactoryInterface::LABEL_NONE)) {
            $legendWidget->setAttribute('icon-before', $this->getAttribute('icon-before', FALSE));
            $this->prepend($legendWidget);
        }

        return parent::render();
    }

}
