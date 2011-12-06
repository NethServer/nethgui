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
 * 
 * 
 */
class DialogBox implements \Serializable
{

    private $message;
    private $type;
    private $actions;
    private $module;
    private $id;
    private $transient;
    private $dismissed;

    public function __construct(\Nethgui\Core\ModuleInterface $module, $message, $actions = array(), $type = \Nethgui\Core\CommandFactoryInterface::NOTIFY_SUCCESS)
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
        $this->type = $type;
        $this->dismissed = FALSE;
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

            // An action with submit data causes the dialog to be persistent
            if ( ! empty($action[2]) && is_null($this->transient)) {
                $this->transient = FALSE;
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

    public function getType()
    {
        return $this->type;
    }

    public function isTransient()
    {
        return $this->transient !== FALSE;
    }

    public function getId()
    {
        if (is_null($this->id)) {
            $this->id = 'dlg' . substr(md5($this->serialize() . microtime()), 0, 6);
        }

        return $this->id;
    }

    public function serialize()
    {
        return serialize(array($this->message, $this->actions, $this->type, $this->module, $this->id, $this->transient, $this->dismissed));
    }

    public function unserialize($serialized)
    {
        $args = unserialize($serialized);
        list($this->message, $this->actions, $this->type, $this->module, $this->id, $this->transient, $this->dismissed) = $args;
    }

    public function dismiss()
    {
        $this->dismissed = TRUE;
    }

    public function isDismissed()
    {
        return $this->dismissed === TRUE;
    }

//    public function asArray(\Nethgui\Core\TranslatorInterface $translator)
//    {
//        $a = array();
//
//        $a['dialogId'] = $this->getId();
//        $a['type'] = $this->getType();
//        $a['transient'] = $this->isTransient();
//        $message = $this->getMessage();
//        $a['message'] = $translator->translate($this->getModule(), $message[0], $message[1]);
//        $a['actions'] = $this->getActions();
//
//        return $a;
//    }
}
