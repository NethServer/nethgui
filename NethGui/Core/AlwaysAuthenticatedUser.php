<?php
/**
 * NethGui
 *
 * @package Core
 *
 */

/**
 * TODO: describe class
 *
 * @deprecated Substitute with the complete implementation in version Phi.
 * @package Core
 */
class NethGui_Core_AlwaysAuthenticatedUser implements NethGui_Core_UserInterface
{

    private $credentials;
    private $dialogs;

    public function __construct()
    {
        session_name('NethGui');
        if (session_id() == '') {
            session_start();
        }

        foreach (array('credentials', 'dialogs') as $member) {
            if (isset($_SESSION[$member])) {
                $this->{$member} = unserialize($_SESSION[$member]);
            } else {
                $this->{$member} = array();
            }
        }
    }

    public function __destruct()
    {
        foreach (array('credentials', 'dialogs') as $member) {
            $_SESSION[$member] = serialize($this->{$member});
        }
    }

    public function getCredential($credentialName)
    {
        if ( ! isset($this->credentials[$credentialName])) {
            return NULL;
        }
        return $this->credentials[$credentialName];
    }

    public function getCredentials()
    {
        return array_keys($this->credentials);
    }

    public function isAuthenticated()
    {
        return TRUE;
    }

    public function setAuthenticated($status)
    {
        
    }

    public function setCredential($credentialName, $credentialValue)
    {
        $this->credentials[$credentialName] = $credentialValue;
    }

    public function hasCredential($credentialName)
    {
        return isset($this->credentials[$credentialName]);
    }

    public function showDialogBox(NethGui_Core_DialogBox $dialog)
    {       
        $this->dialogs[$dialog->getId()] = $dialog;
    }

    public function dismissDialogBox($dialogId)
    {
        if(array_key_exists($dialogId, $this->dialogs)) {
            unset($this->dialogs[$dialogId]);
        }
    }

    public function getDialogBoxes()
    {
        return $this->dialogs;
    }

}

