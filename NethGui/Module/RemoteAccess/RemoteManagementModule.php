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

    public function getDescription()
    {
        return "Controllo di accesso al server-manager.";
    }

    public function initialize()
    {
        parent::initialize();
        $this->declareParameter('networkAddress', '/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}|^$)/');
        $this->declareParameter('networkMask', '/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}|^$)/');
    }

    private function readNetworkData()
    {
        $confDb = $this->getHostConfiguration();

        // Value in property ValidFrom is stored as a comma separated value list
        // of network-address/network-mask couples.
        $network =
            explode('/',
                current(
                    explode(',',
                        $confDb->setDb('configuration')->getProp('httpd-admin', 'ValidFrom')
                    )
                )
            )
        ;

        return $network;
    }

    private function writeValidFrom($networkAddress, $networkMask)
    {
           $confDb = $this->getHostConfiguration();

           $validFrom = $networkAddress . '/' . $networkMask;

           $confDb->setDB('configuration')->setProp('httpd-admin', array('ValidFrom' => $validFrom));
    }

    public function process(NethGui_Core_ResponseInterface $response)
    {
        list($networkAddress, $networkMask) = $this->readNetworkData();

        $changedValues = $this->parameters['networkAddress']
            || $this->parameters['networkMask'];
        
        if ($changedValues) {
            $this->writeValidFrom($this->parameters['networkAddress'], $this->parameters['networkMask']);
        }
        else
        {
            $this->parameters['networkMask'] = $networkMask;
            $this->parameters['networkAddress'] = $networkAddress;
        }

        if ($response->getFormat() === NethGui_Core_ResponseInterface::HTML)
        {
            $response->setViewName('NethGui_View_RemoteAccess_RemoteManagementView');
        }

        $this->fillResponse($response);
    }

}
