<?php

final class RemoteAccessModule extends FormModule implements TopModuleInterface {

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
        foreach (array('Pptp', 'RemoteManagement', 'Ssh', 'Ftp') as $dependency)
        {
            require_once('RemoteAccess/' . $dependency . 'Module.php');
            $childModuleClass = $dependency . 'Module';
            $childModule = new $childModuleClass();
            $childModule->setHostConfiguration($this->hostConfiguration);
            $this->addChild($childModule);
        }
    }

    protected function decorate($output, Response $response)
    {
        // Append SAVE button.
        $output .= '<div style="text-align: right"><input id="' . $response->getWidgetId($this, 'save') . '" name="' . $response->getParameterName($this, 'save') . '" type="submit" value="Save" /></div>';
        return parent::decorate($output, $response);
    }

}