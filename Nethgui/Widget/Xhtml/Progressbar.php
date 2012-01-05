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
class Progressbar extends \Nethgui\Widget\XhtmlWidget
{

    protected function renderContent()
    {
        $name = $this->getAttribute('name');
        $flags = $this->getAttribute('flags', 0);
        $template = $this->getAttribute('template', '${0}%');

        $value = $this->view[$name];

        $cssClass = 'Progressbar';

        if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::STATE_DISABLED) {
            $cssClass .= ' disabled';
        }

        $cssClass .= ' ' . $this->getClientEventTarget();

        $content = $this->openTag('div', array('class' => $cssClass));

        if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::STATE_UNOBSTRUSIVE) {
            $content .= $template;
        } elseif (is_numeric($value)) {
            $content .= htmlspecialchars(strtr($template, array('${0}' => $value)));
        } else {
            $content .= htmlspecialchars($value);
        }
        $content .= $this->closeTag('div');

        return $content;
    }

}
