<?php
/**
 * @package Modules
 */

/**
 * TODO: describe class
 *
 * @package Modules
 */
class NethGui_Module_LocalNetwork extends NethGui_Core_Module_Composite implements NethGui_Core_TopModuleInterface
{
    public function getParentMenuIdentifier()
    {
        return NULL;
    }

    public function initialize()
    {
        parent::initialize();

        $dialog = new NethGui_Core_Module_TableDialog(
            'DlgLocalNetwork',
            'NethGui_View_LocalNetwork_Dialog',
            array(
                array('network', self::VALID_IPv4, NULL),
                array('mask', self::VALID_IPv4, NULL),
                array('router', self::VALID_IP_OR_EMPTY, FALSE, NULL),
            )
        );

        $columns = array('network', 'Mask', 'Router', 'SystemLocalNetwork');
        $tableModule = new NethGui_Core_Module_TableController('networks', 'network', $columns, $dialog);
        $this->addChild($tableModule);
    }

}

