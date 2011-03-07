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
final class NethGui_Module_RemoteAccess_RemoteManagementModule extends NethGui_Core_StandardModule
{

    public function getDescription()
    {
        return "Controllo di accesso al server-manager.";
    }

    public function bind(NethGui_Core_RequestInterface $request)
    {
        parent::bind($request);

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

        if ($request->hasParameter('networkMask'))
        {
            $this->parameters['networkMask'] = $request->getParameter('networkMask');
        } else
        {
            $this->parameters['networkMask'] = $network[1];
        }

        if ($request->hasParameter('networkAddress'))
        {
            $this->parameters['networkAddress'] = $request->getParameter('networkAddress');
        } else
        {
            $this->parameters['networkAddress'] = $network[0];
        }
    }

    public function validate(NethGui_Core_ValidationReportInterface $report)
    {
        parent::validate($report);
        
        $pattern = '/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/';
        if(preg_match($pattern, $this->parameters['networkMask']) == 0)
        {
            $report->addError('networkMask', 'Invalid network mask');
        }
        if(preg_match($pattern, $this->parameters['networkAddress']) == 0)
        {
            $report->addError('networkAddress', 'Invalid network address');
        }
    }

    public function process(NethGui_Core_ResponseInterface $response)
    {
        $valueHasChanged = false;

        if($valueHasChanged) {
            $confDb = $this->getHostConfiguration();

           // TODO: setKey...
        }

        $response->setViewData($this, $this->parameters);
        
        if($response->getViewType() === NethGui_Core_ResponseInterface::HTML)
        {
            $response->setViewName($this, 'NethGui_View_RemoteAccess_RemoteManagementView');
        }
        // TODO: cleanup
        //elseif($response->getViewType() === NethGui_Core_ResponseInterface::JS)
        //{
        //    $response->setViewName($this, 'NethGui_View_RemoteAccess_RemoteManagementView');
        //}
    }

}
