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

        $content .= $this->openTag('div', array('class' => $this->getAttribute('class', 'Tabs')));

        if ($this->hasChildren()) {
            $content .=$this->openTag('ul', array('class' => 'TabList tabList'));

            foreach ($this->getChildren() as $child) {
                $page = $child->getAttribute('name');
                $content .= $this->openTag('li');
                $content .= $this->openTag('a', array('href' => '#' . $this->view->getUniqueId($page)));
                $content .= htmlspecialchars($this->view->translate($page . '_Title'));
                $content .= $this->closeTag('a');
                $content .= $this->closeTag('li');
            }

            $content .=$this->closeTag('ul');
        }

        $content .= $this->renderChildren();

        $content .= $this->closeTag('div');

        return $content;
    }

    public function insert(\Nethgui\Renderer\WidgetInterface $widget)
    {
        if ($widget instanceof \Panel) {
            $widget->setAttribute('class', $widget->getAttribute('class', '') . ' ' . $this->getAttribute('tabClass', 'TabPanel tab-panel'));
            parent::insert($widget);            
        } else {
            $panel = new Panel($this->view);
            parent::insert($panel);
            $panel
                ->setAttribute('name', $widget->getAttribute('name'))
                ->setAttribute('class', $this->getAttribute('tabClass', 'TabPanel tab-panel'))
                ->insert($widget);
        }

        return $this;
    }

}
