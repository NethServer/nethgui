<?php
namespace Nethgui\Module\Help;

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
 * Abstract Help Widget class
 */
class Widget extends \Nethgui\Widget\AbstractWidget
{

    protected function renderContent()
    {
        $whatToDo = $this->getAttribute('do');
        $view = NULL;

        if ($whatToDo === 'inset') {
            $view = $this->view->offsetGet($this->getAttribute('name'));
        } elseif ($whatToDo === 'literal' && $this->getAttribute('isPluginPlaceholder') === TRUE) {
            return $this->getAttribute('data');
        } elseif ($whatToDo === 'literal' && $this->getAttribute('isPlugin') === TRUE) {
            $view = $this->getAttribute('data');
        }

        if ($view instanceof \Nethgui\View\ViewInterface) {
            $renderer = $this->view->spawnRenderer($view);
            $renderer->nestingLevel = $this->view->nestingLevel + 1;
            return $renderer->render();
        }

        return parent::renderContent();
    }

    public function insertPlugins($name = 'Plugin')
    {
        $module = $this->view->getModule();

        if ($module instanceof \Nethgui\Controller\Table\PluggableAction) {
            $module = $module->getParent();
        }

        $pattern = str_replace('\\', '_', get_class($module)) . '_' . $name . '_*.rst';

        $pluginPlaceholder = $this->view->literal('{{{INCLUDE ' . $pattern . '}}}');
        $pluginPlaceholder->setAttribute('isPluginPlaceholder', TRUE);
        $this->insert($pluginPlaceholder);

        $pluginsPanel = $this->view->panel();
        
        foreach ($this->view[$name] as $pluginView) {
            if ($pluginView instanceof \Nethgui\View\ViewInterface) {
                $pluginsPanel->insert(
                    $this->view->literal($pluginView)
                        ->setAttribute('isPlugin', TRUE)
                );
            }
        }

        $this->insert($pluginsPanel);
        
        return $this;
    }

}
