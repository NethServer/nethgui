<?php
/**
 * @package Module
 */

/**
 * @todo describe class
 * @package Module
 */
class NethGui_Module_LocalNetwork extends NethGui_Core_Table_Controller implements NethGui_Core_TopModuleInterface
{

    public function getParentMenuIdentifier()
    {
        return "Administration";
    }

    public function __construct()
    {
        $columns = array('network', 'Mask', 'Router', 'SystemLocalNetwork', 'Actions');
        parent::__construct('LocalNetwork', 'networks', 'network', $columns);
    }

}

