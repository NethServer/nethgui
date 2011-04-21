<?php
/**
 * @package Core
 * @subpackage Table
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * @package Core
 * @subpackage Table
 */
class NethGui_Core_Table_ActionDelete extends NethGui_Core_Module_Action
{

    public function initialize()
    {
        parent::initialize();
        $this->declareParameter('key', $this->getValidator()->notEmpty());
        $this->viewTemplate = 'NethGui_Core_View_ActionDelete';
    }

    public function bind(NethGui_Core_RequestInterface $request)
    {
        parent::bind($request);
        if(is_null($this->parameters['key'])) {
            $arguments = $this->getArguments();
            $this->parameters['key'] = $arguments[0];
        }
    }

    public function process()
    {
        parent::process();
        $db = $this->getParent()->getDatabase();
        $success = $db->deleteKey($this->parameters['key']);
        
        if(!$success) {
            throw new NethGui_Exception_Process('Deletion of key ' . $key . ' failed!');
        }
    }

}