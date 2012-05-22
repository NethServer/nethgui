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
 * Tabs control
 * 
 * 
 * @see http://jqueryui.com/demos/tabs/
 * @since 1.0
 * @author Davide Principi <davide.principi@nethesis.it>
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

    /**
     * Insert plugin module views found under the view's $pluginName member
     * 
     * @param string $pluginName Optional - Default "Plugin"
     * @return \Nethgui\Widget\Xhtml\Tabs
     */
    public function insertPlugins($pluginName = 'Plugin')
    {

        $view = $this->view;

        $pluginTabs = array();
        foreach ($view[$pluginName] as $pluginView) {
            $pluginModule = $pluginView->getModule();
            if ($pluginModule instanceof \Nethgui\Module\ModuleInterface) {

                $cat = $pluginModule->getAttributesProvider()->getCategory();

                if ( ! isset($pluginTabs[$cat])) {
                    // add a panel for the new Category:
                    $pluginTabs[$cat] = $view->panel()
                        ->setAttribute('name', $cat)
                        ->setAttribute('title', $pluginView->translate($cat . '_Title'))
                    ;
                }

                // add plugin view to the Category
                $pluginTabs[$cat]->insert($view->literal($pluginView));
            } else {
                $tabs->insert($view->literal($pluginView)); #add a new tab
            }
        }

        ksort($pluginTabs);
        foreach ($pluginTabs as $tab) {
            $this->insert($tab);
        }

        return $this;
    }

}
