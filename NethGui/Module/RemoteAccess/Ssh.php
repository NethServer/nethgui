<?php
/**
 * NethGui
 *
 * @package Modules
 */

/**
 * TODO: describe class
 *
 * @package Modules
 * @subpackage RemoteAccess
 */
class NethGui_Module_RemoteAccess_Ssh extends NethGui_Core_Module_Standard
{

    public function initialize()
    {
        parent::initialize();
        $this->declareParameter('sshdPort', '/[0-9]*/', '22');
        $this->declareParameter('allowPassword', '/.*/');
        $this->declareParameter('allowRootAccess', '/.*/');
        $this->declareParameter('accessMode', '/(internet|none|local)/', 'none');

        
        $this->constants['accessModeOptions'] = array(
            'local' => 'rete locale',
            'internet' => 'tutta internet',
            'none' => 'nessun host',
        );
    }

}