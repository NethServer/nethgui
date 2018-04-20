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
class Form extends Panel implements \Nethgui\Utility\SessionConsumerInterface
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

        // Change the default enctype value if required by the view
        if ( ! $this->hasAttribute('enctype') && $this->getAttribute('flags') & \Nethgui\Renderer\WidgetFactoryInterface::FORM_ENC_MULTIPART) {
            $this->setAttribute('enctype', 'multipart/form-data');
        };

        // Clear the INSET_FORM flag as the form is now rendered.
        $this->getRenderer()->rejectFlag(\Nethgui\Renderer\WidgetFactoryInterface::INSET_FORM);

        $attributes = array(
            'method' => $this->getAttribute('method', 'post'),
            'action' => $action,
            'class' => 'Form ' . $this->getClientEventTarget(),
            'enctype' => $this->getAttribute('enctype', 'application/x-www-form-urlencoded'),
        );

        // Change default panel wrap tag:
        $this->setAttribute('tag', $this->getAttribute('tag', FALSE));

        $security = $this->session->retrieve('SECURITY');

        $content = '';
        $content .= $this->openTag('form', $attributes);
        $content .= parent::renderContent();
        if(isset($security['csrfToken'])) {
            $content .= $this->controlTag('input', 'csrfToken', 0, '', array(
                'class' => FALSE,
                'id' => FALSE,
                'name' => 'csrfToken',
                'type' => 'hidden',
                'value' => $security['csrfToken'][0])
            );
        }
        $content .= $this->closeTag('form');
        return $content;
    }

    public function setSession(\Nethgui\Utility\SessionInterface $session)
    {
        $this->session = $session;
        return $this;
    }
}
