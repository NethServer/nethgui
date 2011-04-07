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
            $this->getValidator()->memberOf('disabled', 'anyNetwork', 'localNetwork'),
            array(
                array('configuration', 'ftp', 'status'),
                array('configuration', 'ftp', 'access')
            )
        );

        $this->declareParameter(
            'acceptPasswordFromAnyNetwork',
            '/^1?$/',
            array('configuration', 'ftp', 'LoginAccess'),
            ''
        );

        $this->declareImmutable('serviceStatusOptions', array(
            'anyNetwork' => 'Consenti accesso da qualsiasi rete', // TODO: use translator function
            'localNetwork' => 'Consenti accesso da reti locali',
            'disabled' => 'Disabilitato'
            )
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
