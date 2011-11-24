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
 * UserInterface provides access to the login information of the current user.
 *
 */
interface UserInterface extends \ArrayAccess
{

    /**
     * @return boolean
     */
    public function isAuthenticated();

    /**
     * @param bool $status
     * @return UserInterface
     */
    public function setAuthenticated($status);

    /**
     * @param string $credentialName
     * @param mixed $credentialValue
     * @return UserInterface
     */
    public function setCredential($credentialName, $credentialValue);

    /**
     * @param string $credentialName
     * @return mixed
     */
    public function getCredential($credentialName);

    /**
     * @return array
     */
    public function getCredentials();

    public function hasCredential($credentialName);

    /**
     * Add a new Dialog Box in current user session, waiting for the user answer.
     * The answer is handled through NotificationArea module, which is responsible
     * for the dialog dismission.
     * The dialogs that don't expect an answer are dismissed after being shown.
     *
     * @param \Nethgui\Core\ModuleInterface $module
     * @param string $message
     * @param array $actions
     * @param integer $type
     * @return UserInterface
     */
    public function showDialogBox(\Nethgui\Core\ModuleInterface $module, $message, $actions = array(), $type = DialogBox::NOTIFY_SUCCESS);

    public function getDialogBoxes();

    /**
     * @return UserInterface
     */
    public function dismissDialogBox($dialogId);

    /**
     * @return UserInterface
     */
    public function traceProcess(\Nethgui\System\ProcessInterface $process, $name = NULL);

    /**
     * @return array An array of \Nethgui\System\ProcessInterface traced objects
     * @see \Nethgui\System\ProcessInterface
     */
    public function getTracedProcesses();

    /**
     * @return \Nethgui\System\ProcessInterface
     */
    public function getTracedProcess($name);

    /**
     * Get the current language code
     * @return string ISO 639-1 language code (2 characters).
     */
    public function getLanguageCode();   

}

