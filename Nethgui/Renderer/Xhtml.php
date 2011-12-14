<?php
namespace Nethgui\Renderer;

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
 * Enanches the abstract renderer with the wiget factory interface
 *
 * Fragments of the view string representation can be generated through the widget objects
 * returned by the factory interface.
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
class Xhtml extends TemplateRenderer
{

    /**
     *
     * @param \Nethgui\Core\ViewInterface $view
     * @return \Nethgui\Renderer\Xhtml
     */
    public function spawnRenderer(\Nethgui\Core\ViewInterface $view)
    {
        return new static($view, $this->getTemplateResolver(), $this->getDefaultFlags());
    }

    protected function createWidget($widgetType, $attributes = array())
    {
        $className = 'Nethgui\Widget\Xhtml\\' . ucfirst($widgetType);

        $o = new $className($this);

        foreach ($attributes as $aname => $avalue) {
            $o->setAttribute($aname, $avalue);
        }

        return $o;
    }

}
