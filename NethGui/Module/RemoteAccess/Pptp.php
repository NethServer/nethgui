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
class NethGui_Module_RemoteAccess_Pptp extends NethGui_Core_Module_Standard
{

    public function initialize()
    {
        parent::initialize();
        $this->declareParameter(
            'client',
            '/\d+/',
            $this->getHostConfiguration()->getAdapter('configuration', 'pptpd', 'sessions')
        );

        $this->declareParameter(
            'status',
            '/(enabled|disabled)/',
            $this->getHostConfiguration()->getAdapter('configuration', 'pptpd', 'status')
        );
       
    }

}
