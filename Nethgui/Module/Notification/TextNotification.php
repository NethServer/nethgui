<?php
namespace Nethgui\Module\Notification;

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
class TextNotification extends \Nethgui\Module\Notification\AbstractNotification
{

    private $message;
    private $module;

    public function __construct(\Nethgui\Module\ModuleInterface $module, $message, $style = 0)
    {
        if ( ! $module instanceof ModuleSurrogate) {
            $module = new ModuleSurrogate($module);
        }

        // Sanitize the $message parameter: must be a couple <string, params[]>
        if ( ! is_array($message)) {
            $message = array($message, array());
        }

        $this->module = $module;
        $this->message = $message;

        parent::__construct($style, NULL, TRUE);
    }

    public function getModule()
    {
        return $this->module;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $view['title'] = $view->translate($this->message[0], $this->message[1]);
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
        return serialize(array($p, $this->module, $this->message));
    }

    public function unserialize($serialized)
    {
        list($p, $this->module, $this->message) = unserialize($serialized);
        parent::unserialize($p);
    }
}
