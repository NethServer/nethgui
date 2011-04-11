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
            'NethGui_View_LocalNetwork_Dialog',
            array(
                array('network', FALSE, NULL),
                array('mask', FALSE, NULL),
                array('router', FALSE, NULL),
            )
        );

        $tableModule = new NethGui_Core_Module_Table('networks', 'network', $dialog);
        $this->addChild($tableModule);
    }

}

