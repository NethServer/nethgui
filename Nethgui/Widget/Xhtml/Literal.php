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
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @internal
 */
class Literal extends \Nethgui\Widget\XhtmlWidget
{

    protected function getJsWidgetTypes()
    {
        return array();
    }

    protected function renderContent()
    {
        $value = $this->getAttribute('data', '');
        $flags = $this->getAttribute('flags', 0);
        $content = '';

        if ($value instanceof \Nethgui\View\ViewInterface) {
            $content = $this->getRenderer()->spawnRenderer($value)->setDefaultFlags($flags | $this->getRenderer()->getDefaultFlags())->render();
        } else {
            $content = (String) $value;
        }

        if ($this->getAttribute('hsc', FALSE) === TRUE) {
            $content = htmlspecialchars($content);
        }

        return $content;
    }

}
