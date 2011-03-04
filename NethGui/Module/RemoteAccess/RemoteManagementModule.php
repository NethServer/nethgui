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
        if ($request->hasParameter('networkMask'))
        {
            $this->parameters['networkMask'] = $request->getParameter('networkMask');
        }

        if ($request->hasParameter('networkAddress'))
        {
            $this->parameters['networkAddress'] = $request->getParameter('networkAddress');
        }
    }

    public function process()
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

        // Reading default values:
        if ( ! isset($this->parameters['networkMask']))
        {

            $this->parameters['networkMask'] = $network[1];
        } else
        {
            // TODO: check if value has changed
            // call SME
        }
        if ( ! isset($this->parameters['networkAddress']))
        {
            $this->parameters['networkAddress'] = $network[0];
        } else
        {
            // TODO: check if value has changed
            // call SME
        }
    }

    public function renderViewJavascript(NethGui_Core_ResponseInterface $response)
    {
        return '/* javascript */';
    }

    public function renderViewHtml(NethGui_Core_ResponseInterface $response)
    {
        return $this->renderCodeIgniterView($response, 'RemoteAccess/RemoteManagementView.php');
    }

}
