<?php
/**
 * @package Core
 */

namespace Nethgui\Client;

/**
 * UserInterface provides access to the login information of the current user.
 *
 * @package Core
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

