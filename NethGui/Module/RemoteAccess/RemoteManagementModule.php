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
final class NethGui_Module_RemoteAccess_RemoteManagementModule extends NethGui_Core_Module_Standard
{

    private $command = 'NOOP';

    public function getDescription()
    {
        return "Controllo di accesso al server-manager.";
    }

    public function initialize()
    {
        parent::initialize();
        $this->declareParameter('networkAddress', '/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/');
        $this->declareParameter('networkMask', '/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/');
    }

    public function bind(NethGui_Core_RequestInterface $request)
    {
        parent::bind($request);

        /*
         * If network parameters are not set by Request, read values from db.
         */
        if ( ! isset($this->parameters['networkAddress'], $this->parameters['networkMask'])) {
            list($networkAddress, $networkMask) = $this->readValidFrom();
            $this->parameters['networkAddress'] = $networkAddress;
            $this->parameters['networkMask'] = $networkMask;
            $this->command = 'NOOP';
        } else {
            $this->command = 'UPDATE';
        }

        /*
         * After parameter binding we are sure network parameters are string values.
         */
    }

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
                ->setDb('configuration')
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

        if(! isset($network[1]))
        {
            $network[1] = '';
        }
        
        return $network;
    }

    private function writeValidFrom($networkAddress, $networkMask)
    {
        $validFrom = $networkAddress . '/' . $networkMask;
        $this->getHostConfiguration()
            ->setDB('configuration')
            ->setProp('httpd-admin', array('ValidFrom' => $validFrom))
        ;
    }

    private function deleteValidFrom()
    {
        $this->getHostConfiguration()
            ->setDB('configuration')
            ->delProp('httpd-admin', array('ValidFrom'))
        ;
    }

    public function process(NethGui_Core_ResponseInterface $response)
    {
        switch ($this->command) {
            case 'DELETE':
                $this->deleteValidFrom();
                break;
            case 'UPDATE':
                $this->writeValidFrom($this->parameters['networkAddress'], $this->parameters['networkMask']);
                break;
        }

        if ($response->getFormat() === NethGui_Core_ResponseInterface::HTML)
        {
            $response->setViewName('NethGui_View_RemoteAccess_RemoteManagementView');
        }

        parent::process($response);
    }

}

