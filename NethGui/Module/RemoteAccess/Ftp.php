<?php
/**
 * NethGui
 *
 * @package Modules
 */

/**
 * TODO: describe class
 *
 * This module
 *
 * @package Modules
 * @subpackage RemoteAccess
 */
class NethGui_Module_RemoteAccess_Ftp extends NethGui_Core_Module_Standard
{
    public function initialize()
    {
        parent::initialize();

        $this->db = new NethGui_Core_ParameterSet();

        $this->db['status'] = $this->getHostConfiguration()->getAdapter('configuration', 'ftp', 'status');
        $this->db['access'] = $this->getHostConfiguration()->getAdapter('configuration', 'ftp', 'access');
        $this->db['LoginAccess'] = $this->getHostConfiguration()->getAdapter('configuration', 'ftp', 'LoginAccess');       

        $this->declareParameter(
            'serviceStatus', // parameter name
            '/^(disabled|localNetwork|anyNetwork)$/', // regexp validation
            $this->calcServiceStatus(// mapping function
                $this->db['status'],
                $this->db['access']
            )
        );

        $this->declareParameter(
            'acceptPasswordFromAnyNetwork',
            '/^1?$/',
            $this->db['LoginAccess'] == 'public' ? '1' : ''
        );

        // TODO: use translator function
        $this->constants['serviceStatusOptions'] = array(
            'anyNetwork' => 'Consenti accesso da qualsiasi rete',
            'localNetwork' => 'Consenti accesso da reti locali',
            'disabled' => 'Disabilitato'
        );
    }

    private function calcServiceStatus($status, $access)
    {
        if ($status == 'enabled') {
            if ($access == 'public') {
                return 'anyNetwork';
            } elseif ($access == 'private') {
                return 'localNetwork';
            }
        }

        return 'disabled';
    }

    public function bind(NethGui_Core_RequestInterface $request)
    {
        parent::bind($request);

        switch ($this->parameters['serviceStatus']) {

            case 'disabled':
                $this->db['status'] = 'disabled';
                $this->db['access'] = 'private';
                break;

            case 'localNetwork':
                $this->db['status'] = 'enabled';
                $this->db['access'] = 'private';
                break;

            case 'anyNetwork':
                $this->db['status'] = 'enabled';
                $this->db['access'] = 'public';
                break;
        }

        if ($this->parameters['acceptPasswordFromAnyNetwork'] == 1) {
            $this->db['LoginAccess'] = 'public';
        } else {
            $this->db['LoginAccess'] = 'private';
        }
    }

    public function process()
    {
        parent::process();
        $this->db->save();
    }


}
