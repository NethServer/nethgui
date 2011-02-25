<?php

final class RemoteManagementModule extends StandardModule {

    public function getDescription()
    {
        return "Controllo di accesso al server-manager.";
    }

    public function  renderView(Response $response)
    {
        $output = parent::renderView($response);

        $output = $this->renderCodeIgniterView($response, 'RemoteAccess/RemoteManagementView.php');

        return $output;
    }

}
