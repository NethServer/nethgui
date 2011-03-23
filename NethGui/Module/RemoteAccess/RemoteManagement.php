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
class NethGui_Module_RemoteAccess_RemoteManagement extends NethGui_Core_Module_Standard
{

    private $command = 'NOOP';

    public function getDescription()
    {
        return "Controllo di accesso al server-manager.";
    }

    public function initialize()
    {
        parent::initialize();
        $this->declareParameter('networkAddress', '/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', NULL);
        $this->declareParameter('networkMask', '/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', NULL);
    }

    public function bind(NethGui_Core_RequestInterface $request)
    {
        parent::bind($request);

        if ( ! is_null($this->parameters['networkAddress'])
            && ! is_null($this->parameters['networkMask'])) {
            $this->command = 'UPDATE';
        } else {

            /*
             * If network parameters are set neither by Request nor by declarations,
             * read values from db.
             */
            list($networkAddress, $networkMask) = $this->readValidFrom();
            $this->parameters['networkAddress'] = $networkAddress;
            $this->parameters['networkMask'] = $networkMask;
            $this->command = 'NOOP';
        }

        /*
         * After parameter binding we are sure network parameters are string values.
         */
    }

    /**
     * This implements a GUI behaviour: if both fields are empty we want
     * to DELETE the database key. In this case, we skip normal validation.
     */
    public function validate(NethGui_Core_ValidationReportInterface $report)
    {
        /*
         * Allow clearing both fields to say we want to delete the database key.
         */
        if ($this->parameters['networkAddress'] === ''
            && $this->parameters['networkMask'] === '') {

            /*
             * Substitute database UPDATE action with DELETE.
             */
            if ($this->command == 'UPDATE') {
                $this->command = 'DELETE';
            }

            return;
        }

        parent::validate($report);
    }

    /**
     * Read `ValidFrom` property from SMEdb. If not set, empty strings are
     * returned.
     * 
     * @return array Two element array: (NetworkAddress, NetworkMask)
     */
    private function readValidFrom()
    {
        $validFrom = $this->getHostConfiguration()
                ->getDatabase('configuration')
                ->getProp('httpd-admin', 'ValidFrom')
        ;

        // Value in property ValidFrom is stored as a comma separated value list
        // of network-address/network-mask couples.
        $network =
            explode('/',
                current(
                    explode(',', $validFrom)
                )
            )
        ;

        if ( ! isset($network[1]))
        {
            $network[1] = '';
        }

        return $network;
    }

    private function writeValidFrom($networkAddress, $networkMask)
    {
        $validFrom = $networkAddress . '/' . $networkMask;
        $this->getHostConfiguration()
            ->getDatabase('configuration')
            ->setProp('httpd-admin', array('ValidFrom' => $validFrom))
        ;
    }

    private function deleteValidFrom()
    {
        $this->getHostConfiguration()
            ->getDatabase('configuration')
            ->delProp('httpd-admin', array('ValidFrom'))
        ;
    }

    public function process()
    {
        parent::process();

        switch ($this->command) {
            case 'DELETE':
                $this->deleteValidFrom();
                break;
            case 'UPDATE':
                $this->writeValidFrom($this->parameters['networkAddress'], $this->parameters['networkMask']);
                break;
        }
    }

}

