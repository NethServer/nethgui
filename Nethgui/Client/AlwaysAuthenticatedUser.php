<?php
/**
 * Nethgui
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
class Nethgui_Client_AlwaysAuthenticatedUser implements Nethgui_Client_UserInterface
{

    /**
     * User authentication credentials
     * @var array
     */
    private $credentials;

    /**
     * Persistent message dialog boxes
     * @var array
     */
    private $dialogs;

    /**
     * Any kind of session data, accessible through the ArrayAccess interface
     * @var array
     */
    private $data;
    
    /**
     * Traced processes
     * @var type
     */
    private $processes;

    /**
     * @var string
     */
    private $languageCode;

    public function __construct()
    {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $this->setLanguageCode($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        } else {
            $this->setLanguageCode('en');
        }

        session_name('Nethgui');
        if (session_id() == '') {
            session_start();
        }

        foreach (array('credentials', 'dialogs', 'data', 'processes') as $member) {
            if (isset($_SESSION[$member])) {
                $this->{$member} = $_SESSION[$member];
            } else {
                $this->{$member} = array();
            }
        }
    }

    public function __destruct()
    {
        foreach (array('credentials', 'dialogs', 'data', 'processes') as $member) {
            $_SESSION[$member] = $this->{$member};
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
        return $this;
    }

    public function setCredential($credentialName, $credentialValue)
    {
        $this->credentials[$credentialName] = $credentialValue;
        return $this;
    }

    public function hasCredential($credentialName)
    {
        return isset($this->credentials[$credentialName]);
    }

    public function showDialogBox(Nethgui_Core_ModuleInterface $module, $message, $actions = array(), $type = Nethgui_Client_DialogBox::NOTIFY_SUCCESS)
    {
        $dialog = new Nethgui_Client_DialogBox($module, $message, $actions, $type);

        if ( ! array_key_exists($dialog->getId(), $this->dialogs))
        {
            $this->dialogs[$dialog->getId()] = $dialog;
        }
        return $this;
    }

    public function dismissDialogBox($dialogId)
    {
        if (array_key_exists($dialogId, $this->dialogs)) {
            unset($this->dialogs[$dialogId]);
        }
        return $this;
    }

    public function getDialogBoxes()
    {
        return $this->dialogs;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function traceProcess(Nethgui_System_ProcessInterface $process, $name = NULL)
    {
        if (is_null($name)) {
            $name = uniqid();
        }

        $this->processes[$name] = $process;
        return $this;
    }

    public function getTracedProcesses()
    {
        return array_values($this->processes);
    }

    public function getTracedProcess($name)
    {
        if ( ! isset($this->processes[$name])) {
            return FALSE;
        }
        return $this->processes[$name];
    }

    /**
     * Set the current language code
     * @param string $code ISO 639-1 language code (2 characters).
     */
    private function setLanguageCode($languageCode)
    {
        if ($languageCode) {
            $this->languageCode = strtolower(substr($languageCode, 0, 2));
        }
    }


    public function getLanguageCode()
    {
        return $this->languageCode;
    }

}
