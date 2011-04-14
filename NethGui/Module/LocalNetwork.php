<?php
/**
 * @package Modules
 */

/**
 * TODO: describe class
 *
 * @package Modules
 */
class NethGui_Module_LocalNetwork extends NethGui_Core_Module_TableController implements NethGui_Core_TopModuleInterface
{

    public function getParentMenuIdentifier()
    {
        return NULL;
    }

    public function __construct()
    {
        $columns = array('network', 'Mask', 'Router', 'SystemLocalNetwork', 'Actions');
        $events = array();

        $dialog = new NethGui_Core_Module_TableDialog(
                'DlgLocalNetwork',
                'NethGui_View_LocalNetwork_Dialog',
                array(
                    array('network', self::VALID_IPv4, NULL),
                    array('Mask', self::VALID_IPv4, NULL),
                    array('Router', self::VALID_IP_OR_EMPTY, FALSE, NULL),
                )
        );

        parent::__construct('LocalNetwork', 'networks', 'network', $columns, $dialog, $events);
    }

    public function prepareColumnActions(NethGui_Core_ViewInterface $view, $mode, $values)
    {
        if(isset($values['SystemLocalNetwork']) && $values['SystemLocalNetwork'] == 'yes') {
            return NULL;
        }
        return parent::prepareColumnActions($view, $mode, $values);
    }


}

