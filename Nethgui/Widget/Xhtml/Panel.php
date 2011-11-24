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
class Panel extends \Nethgui\Widget\XhtmlWidget
{

    public function render()
    {
        $content = '';
        $cssClass = $this->getAttribute('class', FALSE);
        $tag = $this->getAttribute('tag', 'div');

        $flags = $this->getAttribute('flags');
        if ($cssClass && ($flags & \Nethgui\Renderer\WidgetFactoryInterface::STATE_DISABLED)) {
            $cssClass .= ' disabled';
        }

        if ($this->hasAttribute('name') && $this->getAttribute('name') !== FALSE) {
            $id = $this->view->getUniqueId($this->getAttribute('name'));
        } else {
            $id = FALSE;
        }

        $attributes = array(
            'class' => empty($cssClass) ? FALSE : trim($cssClass),
            'id' => $id
        );

        $content .= $this->openTag($tag, $attributes);
        $content .= $this->renderChildren();
        $content .= $this->closeTag($tag);

        return $content;
    }

}
