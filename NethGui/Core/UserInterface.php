<?php
/**
 * @package Core
 */

/**
 * NethGui_Core_UserInterface provides access to the login information of the current user.
 *
 * @package Core
 */
interface NethGui_Core_UserInterface
{

    /**
     * @return boolean
     */
    public function isAuthenticated();

    /**
     * @param bool $status
     */
    public function setAuthenticated($status);

    /**
     * @param string $credentialName
     * @param mixed $credentialValue
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
     */
    public function showDialogBox(NethGui_Core_ModuleInterface $module, $message, $actions = array(), $type = NethGui_Core_DialogBox::NOTIFY_SUCCESS);
    
    public function getDialogBoxes();
    
    public function dismissDialogBox($dialogId); 
    
    public function setRedirect(NethGui_Core_ModuleInterface $module, $path = array());
    
    public function getRedirect();
}

