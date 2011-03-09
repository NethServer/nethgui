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
 */
final class NethGui_Module_RemoteAccessModule extends NethGui_Core_Module_Composite implements NethGui_Core_TopModuleInterface
{

    public function getTitle()
    {
        return "Remote access";

    }

    public function getParentMenuIdentifier()
    {
        return "SecurityModule";

    }

    public function initialize()
    {
        parent::initialize();
        foreach (array('Pptp', 'RemoteManagement', 'Ssh', 'Ftp') as $dependency) {
            $childModuleClass = 'NethGui_Module_RemoteAccess_' . $dependency . 'Module';
            $childModule = new $childModuleClass();
            $childModule->setHostConfiguration($this->getHostConfiguration());
            $this->addChild($childModule);
        }

    }

    public function process(NethGui_Core_ResponseInterface $response)
    {
        parent::process($response);

        log_message('info', 'Format: ' . $response->getFormat());

        if($response->getFormat() === NethGui_Core_ResponseInterface::HTML)
        {
            log_message('info', '$response->setViewName(\'NethGui_Core_View_form\');');
            $response->setViewName('NethGui_Core_View_form');
        }
        $response->setData(array('save' => 1));
    }

}