<?php
/**
 * @package Core
 * @author Davide Principi <davide.principi@nethesis.it>
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
 * @package Core
 */
class NethGui_Core_DialogBox implements Serializable
{
    const NOTIFY_SUCCESS = 0;
    const NOTIFY_WARNING = 1;
    const NOTIFY_ERROR = 2;


    private $message;
    private $type;
    private $actions;
    private $module;
    private $id;

    public function __construct(NethGui_Core_ModuleInterface $module, $message, $actions = array(), $type = self::NOTIFY_SUCCESS)
    {
        if ( ! $module instanceof NethGui_Core_ModuleSurrogate) {
            $module = new NethGui_Core_ModuleSurrogate($module);
        }

        $this->module = $module;
        $this->actions = $this->sanitizeActions($actions);
        $this->message = $message;
        $this->type = $type;
    }

    private function sanitizeActions($actions) {
        $sanitizedActions = array();
        
        foreach($actions as $action) {
            if(is_string($action)) {
                $action = array($action, '', array());
            }
            
            if(!isset($action[1])) {
                $action[1] = '';
            }
            
            if(!isset($action[2])) {
                $action[2] = array();
            }
            
            $sanitizedActions[] = $action;
        }
        
        return $sanitizedActions;
    }

    public function getModule() {
        return $this->module;
    }
    
    public function getActions() {        
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
        return empty($this->actions);
    }

    public function getId()
    {
        if (is_null($this->id)) {
            // $this->id = substr(md5(microtime() . $this->serialize()), 0, 6);
            $this->id = substr(md5($this->serialize()), 0, 6);
        }

        return $this->id;
    }

    public function serialize()
    {
        return serialize(array($this->message, $this->actions, $this->type, $this->module, $this->id));
    }

    public function unserialize($serialized)
    {
        $args = unserialize($serialized);
        list($this->message, $this->actions, $this->type, $this->module, $this->id) = $args;
    }

}