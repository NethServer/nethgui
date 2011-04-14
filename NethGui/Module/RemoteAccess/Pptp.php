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
class NethGui_Module_RemoteAccess_Pptp extends NethGui_Core_Module_Standard
{

    public function initialize()
    {
        parent::initialize();
        $this->declareParameter(
            'client',
            '/\d+/',
            array('configuration', 'pptpd', 'sessions')
        );

        $this->declareParameter(
            'status',
            self::VALID_SERVICESTATUS,
            array('configuration', 'pptpd', 'status')
        );
       
    }

}
