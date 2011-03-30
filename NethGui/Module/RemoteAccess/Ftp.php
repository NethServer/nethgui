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

        $this->declareParameter(
            'serviceStatus', // parameter name
            '/^(disabled|localNetwork|anyNetwork)$/', // regexp validation
            new NethGui_Core_MultipleAdapter(
                array($this, 'readServiceStatus'),
                array($this, 'writeServiceStatus'),
                array(
                    new NethGui_Core_PropSerializer($this->getHostConfiguration()->getDatabase('configuration'), 'ftp', 'status'),
                    new NethGui_Core_PropSerializer($this->getHostConfiguration()->getDatabase('configuration'), 'ftp', 'access'),
                )
            )
        );

        $this->declareParameter(
            'acceptPasswordFromAnyNetwork',
            '/^1?$/',
            new NethGui_Core_MultipleAdapter(
                array($this, 'readAcceptPasswordFromAnyNetwork'),
                array($this, 'writeAcceptPasswordFromAnyNetwork'),
                array(new NethGui_Core_PropSerializer($this->getHostConfiguration()->getDatabase('configuration'), 'ftp', 'LoginAccess'))
            ),
            ''
        );

        // TODO: use translator function
        $this->constants['serviceStatusOptions'] = array(
            'anyNetwork' => 'Consenti accesso da qualsiasi rete',
            'localNetwork' => 'Consenti accesso da reti locali',
            'disabled' => 'Disabilitato'
        );
    }

    /**
     * @codeCoverageIgnore
     * 
     * @param string $status
     * @param string $access
     * @return string
     */
    public function readServiceStatus($status, $access)
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

    /**
     * @codeCoverageIgnore
     *
     * @param string $value
     * @return array
     */
    public function writeServiceStatus($value)
    {
        switch ($value) {
            case 'localNetwork':
                return array('enabled', 'private');

            case 'anyNetwork':
                return array('enabled', 'public');


            case 'disabled':
            default:
                return array('disabled', 'private');
        }
    }

    /**
     * @codeCoverageIgnore
     *
     * @param string $value
     * @return string
     */
    public function readAcceptPasswordFromAnyNetwork($value)
    {
        if ($value == 'public') {
            return 1;
        }

        return '';
    }

    /**
     * @codeCoverageIgnore
     *
     * @param string $value
     * @return array
     */
    public function writeAcceptPasswordFromAnyNetwork($value)
    {
        if ($value == 1) {
            return array('public');
        } else {
            return array('private');
        }
    }

}
