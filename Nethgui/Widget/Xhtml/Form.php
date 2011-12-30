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
 * Wrap FORM tag around a Panel object
 *
 * Attributes
 * - string name the view element that overrides the action attribute
 * - string action the url (relative to the current module) set on the HTML FORM "action" attribute
 * - string method "post" (default), or "get"
 */
class Form extends Panel
{

    protected function renderContent()
    {        
        // Ensure a name is defined:
        if ( ! $this->hasAttribute('name')) {
            $this->setAttribute('name', 'FormAction');
        }

        $name = $this->getAttribute('name');

        if (isset($this->view[$name])) {
            $action = $this->view[$name];
        } else {
            // Rely on action attribute as fallback:
            $action = $this->view->getModuleUrl($this->getAttribute('action', ''));
        }

        // Clear the INSET_FORM flag as the form is now rendered.
        $this->getRenderer()->rejectFlag(\Nethgui\Renderer\WidgetFactoryInterface::INSET_FORM);

        $attributes = array(
            'method' => $this->getAttribute('method', 'post'),
            'action' => $action,
            'class' => 'Form ' . $this->getClientEventTarget(),
        );

        $content = '';
        $content .= $this->openTag('form', $attributes);
        $content .= parent::renderContent();
        $content .= $this->closeTag('form');
        return $content;
    }

}
