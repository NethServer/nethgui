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
 * Renders the given literal string, optionally escaping special html characters
 * through PHP htmlspecialchars() function.
 *
 * Attributes:
 * - `data` any string or object with string representation
 * - `hsc` boolean
 *
 */
class Literal extends \Nethgui\Widget\XhtmlWidget
{

    public function render()
    {
        $value = $this->getAttribute('data', '');

        $content = '';

        $flags = $this->getAttribute('flags', 0);

        if ($value instanceof \Nethgui\Renderer\WidgetFactoryInterface) {
            $valueFlags = $value->getDefaultFlags() | $this->view->getDefaultFlags();
        } else {
            $valueFlags = 0;
        }

        if ($value instanceof \Nethgui\Core\ViewInterface && $this->view instanceof \Nethgui\Core\CommandReceiverInterface) {
            $value = new \Nethgui\Renderer\Xhtml($value, $this->view->getTemplateResolver(), $valueFlags, $this->view);
        }

        $content = (String) $value;

        if ($this->getAttribute('hsc', FALSE) === TRUE) {
            $content = htmlspecialchars($content);
        }

        return $content;
    }

    public function setAttribute($attribute, $value)
    {
        if ($attribute == 'data' && $value instanceof \Nethgui\Core\ViewInterface) {
            parent::setAttribute('name', $value->getModule()->getIdentifier());
        }
        return parent::setAttribute($attribute, $value);
    }

}
