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
final class NethGui_Module_RemoteAccessModule extends NethGui_Core_FormModule implements NethGui_Core_TopModuleInterface
{

    public function getTitle()
    {
        return "Remote access";
    }

    public function getParentMenuIdentifier()
    {
        return "NethGui_Module_SecurityModule";
    }

    public function initialize()
    {
        parent::initialize();
        foreach (array('Pptp', 'RemoteManagement', 'Ssh', 'Ftp') as $dependency) {
            require_once('RemoteAccess/' . $dependency . 'Module.php');
            $childModuleClass = 'NethGui_Module_RemoteAccess_' . $dependency . 'Module';
            $childModule = new $childModuleClass();
            $childModule->setHostConfiguration($this->hostConfiguration);
            $this->addChild($childModule);
        }
    }

    protected function decorate($output, NethGui_Core_Response $response)
    {
        // Append SAVE button.
        $output .= '<div style="text-align: right"><input id="' . $response->getWidgetId($this, 'save') . '" name="' . $response->getParameterName($this, 'save') . '" type="submit" value="Save" /></div>';
        return parent::decorate($output, $response);
    }

}