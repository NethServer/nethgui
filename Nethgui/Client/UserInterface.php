<?php
/**
 * @package Core
 */

/**
 * Nethgui_Client_UserInterface provides access to the login information of the current user.
 *
 * @package Core
 */
interface Nethgui_Client_UserInterface extends ArrayAccess
{

    /**
     * @return boolean
     */
    public function isAuthenticated();

    /**
     * @param bool $status
     * @return Nethgui_Client_UserInterface
     */
    public function setAuthenticated($status);

    /**
     * @param string $credentialName
     * @param mixed $credentialValue
     * @return Nethgui_Client_UserInterface
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
     * @param Nethgui_Core_ModuleInterface $module
     * @param string $message
     * @param array $actions
     * @param integer $type
     * @return Nethgui_Client_UserInterface
     */
    public function showDialogBox(Nethgui_Core_ModuleInterface $module, $message, $actions = array(), $type = Nethgui_Client_DialogBox::NOTIFY_SUCCESS);

    public function getDialogBoxes();

    /**
     * @return Nethgui_Client_UserInterface
     */
    public function dismissDialogBox($dialogId);

    /**
     * @return Nethgui_Client_UserInterface
     */
    public function traceProcess(Nethgui_System_ProcessInterface $process, $name = NULL);

    /**
     * @return array An array of Nethgui_System_ProcessInterface traced objects
     * @see Nethgui_System_ProcessInterface
     */
    public function getTracedProcesses();

    /**
     * @return Nethgui_System_ProcessInterface
     */
    public function getTracedProcess($name);

    /**
     * Get the current language code
     * @return string ISO 639-1 language code (2 characters).
     */
    public function getLanguageCode();   

}

