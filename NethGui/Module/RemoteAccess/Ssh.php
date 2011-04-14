<?php
/**
 * @package NethGui
 * @subpackage Module_RemoteAccess
 */

/**
 * @todo Describe Module class
 * @package NethGui
 * @subpackage Module_RemoteAccess
 */
class NethGui_Module_RemoteAccess_Ssh extends NethGui_Core_Module_Standard
{

    public function initialize()
    {
        parent::initialize();
        $this->declareParameter('sshdPort', '/[0-9]*/', NULL, '22');
        $this->declareParameter('allowPassword', '/.*/', NULL, '');
        $this->declareParameter('allowRootAccess', '/.*/', NULL, '');
        $this->declareParameter('accessMode', '/(internet|none|local)/', NULL, 'none');
        $this->declareImmutable('accessModeOptions', array(
            'local' => 'rete locale',
            'internet' => 'tutta internet',
            'none' => 'nessun host',
            )
        );
    }

}