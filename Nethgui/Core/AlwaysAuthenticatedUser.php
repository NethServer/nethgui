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
class Nethgui_Core_AlwaysAuthenticatedUser implements Nethgui_Core_UserInterface
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
     * Command to be executed on the client side.
     * @var type
     */
    private $clientCommands = array();

    /**
     * Traced processes
     * @var type
     */
    private $processes;

    public function __construct()
    {
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

    public function showDialogBox(Nethgui_Core_ModuleInterface $module, $message, $actions = array(), $type = Nethgui_Core_DialogBox::NOTIFY_SUCCESS)
    {
        $dialog = new Nethgui_Core_DialogBox($module, $message, $actions, $type);

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

    public function addClientCommandEnable(Nethgui_Core_ModuleInterface $action)
    {
        $this->addClientCommand(new Nethgui_Client_Command_Enable($action));
        return $this;
    }

    public function addClientCommandActivate(Nethgui_Core_ModuleInterface $action, Nethgui_Core_ModuleInterface $cancelAction = NULL)
    {
        $this->addClientCommand(new Nethgui_Client_Command_Activate($action, $cancelAction));
        return $this;
    }

    public function addClientCommand(Nethgui_Client_CommandInterface $command)
    {
        $this->clientCommands[] = $command;
        return $this;
    }

    public function getClientCommands()
    {
        return $this->clientCommands;
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

}
