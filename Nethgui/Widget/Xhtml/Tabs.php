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
class Tabs extends \Nethgui\Widget\XhtmlWidget
{

    protected function renderContent()
    {
        $content = '';

        $attributes = array('class' => $this->getAttribute('class', 'Tabs'));
        if ($this->hasAttribute('receiver')) {
            $attributes['id'] = $this->view->getUniqueId($this->getAttribute('receiver'));
        }

        $content .= $this->openTag('div', $attributes);

        if ($this->hasChildren()) {
            $content .= $this->openTag('ul', array('class' => 'tabList'));

            foreach ($this->getChildren() as $index => $child) {
                if ( ! $child->hasAttribute('receiver')) {
                    $child->setAttribute('receiver', $child->getAttribute('name', $this->view->getUniqueId(sprintf('tab-%d-%d', $this->getInstanceCounter(), $index))));
                }
                $childTitle = $child->getAttribute('title', $this->getTranslateClosure($child->getAttribute('receiver') . '_Title'));
                $content .= $this->openTag('li');
                $content .= $this->openTag('a', array('href' => '#' . $this->view->getUniqueId($child->getAttribute('receiver'))));
                $content .= htmlspecialchars($childTitle);
                $content .= $this->closeTag('a');
                $content .= $this->closeTag('li');
            }

            $content .=$this->closeTag('ul');
        }

        $content .= $this->renderChildren();

        $content .= $this->closeTag('div');

        return $content;
    }

}
