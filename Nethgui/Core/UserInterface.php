<?php
/**
 * @package Core
 */

/**
 * Nethgui_Core_UserInterface provides access to the login information of the current user.
 *
 * @package Core
 */
interface Nethgui_Core_UserInterface extends ArrayAccess
{

    /**
     * @return boolean
     */
    public function isAuthenticated();

    /**
     * @param bool $status
     * @return Nethgui_Core_UserInterface
     */
    public function setAuthenticated($status);

    /**
     * @param string $credentialName
     * @param mixed $credentialValue
     * @return Nethgui_Core_UserInterface
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
     * @return Nethgui_Core_UserInterface
     */
    public function showDialogBox(Nethgui_Core_ModuleInterface $module, $message, $actions = array(), $type = Nethgui_Core_DialogBox::NOTIFY_SUCCESS);

    public function getDialogBoxes();

    /**
     * @return Nethgui_Core_UserInterface
     */
    public function dismissDialogBox($dialogId);

    /**
     * @return Nethgui_Core_UserInterface
     */
    public function addClientCommand(Nethgui_Client_CommandInterface $command);

    /**
     * @return Nethgui_Core_UserInterface
     */
    public function addClientCommandEnable(Nethgui_Core_ModuleInterface $action);

    /**
     * @return Nethgui_Core_UserInterface
     */
    public function addClientCommandActivate(Nethgui_Core_ModuleInterface $action, Nethgui_Core_ModuleInterface $cancelAction = NULL);

    public function getClientCommands();
}

