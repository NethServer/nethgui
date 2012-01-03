<?php
namespace Nethgui\Client;

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
 * A Dialog Box object shows a message to the User.
 * 
 * One or more buttons can be given to perform some action. 
 * 
 * A Dialog Box can be transient or persistent. Both types are shown to the User
 * until they are dismissed. A transient dialog box disappears after a UI update.
 * Persistent ones disappear after the User clicks on a button.
 * 
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class DialogBox extends \Nethgui\Client\AbstractNotification
{

    private $message;
    private $actions;
    private $module;

    public function __construct(\Nethgui\Core\ModuleInterface $module, $message, $actions = array(), $style = 0)
    {
        if ( ! $module instanceof ModuleSurrogate) {
            $module = new ModuleSurrogate($module);
        }

        // Sanitize the $message parameter: must be a couple <string, params[]>
        if ( ! is_array($message)) {
            $message = array($message, array());
        }

        $this->module = $module;
        $this->actions = $this->sanitizeActions($actions);
        $this->message = $message;

        parent::__construct($style, 'Message', count($this->actions) === 0);
    }

    private function sanitizeActions($actions)
    {
        $sanitizedActions = array();

        foreach ($actions as $action) {
            if (is_string($action)) {
                $action = array($action, '', array());
            }

            if ( ! isset($action[1])) {
                $action[1] = '';
            }

            if ( ! isset($action[2])) {
                $action[2] = array();
            }

            $sanitizedActions[] = $action;
        }

        return $sanitizedActions;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function getActions()
    {
        return $this->actions;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function prepareView(\Nethgui\Core\ViewInterface $view)
    {
        parent::prepareView($view);
        $view['title'] = $view->translate($this->message[0], $this->message[1]);
        $view['actions'] = $this->actions;
    }

    public function renderXhtml(\Nethgui\Renderer\Xhtml $renderer)
    {
        $panel = parent::renderXhtml($renderer);
        $message = $renderer->textLabel('title')->setAttribute('icon-before', 'ui-icon-info');
        return $panel->insert($message);
    }

    public function serialize()
    {
        $p = parent::serialize();
        return serialize(array($p, $this->module, $this->actions, $this->message));
    }

    public function unserialize($serialized)
    {
        list($p, $this->module, $this->actions, $this->message) = unserialize($serialized);
        parent::unserialize($p);
    }
}
