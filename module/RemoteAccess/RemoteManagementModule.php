<?php

final class RemoteManagementModule extends StandardModule {

    public function getDescription()
    {
        return "Controllo di accesso al server-manager.";
    }

    public function bind(RequestInterface $request)
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
        // Reading default values:
        if ( ! isset($this->parameters['networkMask']))
        {
            $this->parameters['networkMask'] = $this->hostConfiguration->read(array('configuration', 'validFromNetworkMask'));
        }
        else
        {
            // TODO: check if value has changed
            $this->hostConfiguration->write(array('configuration', 'validFromNetworkMask'), $this->parameters['networkMask']);
        }
        if ( ! isset($this->parameters['networkAddress']))
        {
            $this->parameters['networkAddress'] = $this->hostConfiguration->read(array('configuration', 'validFromNetworkAddress'));
        }
        else
        {
            // TODO: check if value has changed
            $this->hostConfiguration->write(array('configuration', 'validFromNetworkMask'), $this->parameters['networkAddress']);
        }
    }

    public function renderView(Response $response)
    {
        $output = parent::renderView($response);
        $output = $this->renderCodeIgniterView($response, 'RemoteAccess/RemoteManagementView.php');
        return $output;
    }

}
