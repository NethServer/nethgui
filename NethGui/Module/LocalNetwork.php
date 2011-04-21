<?php
/**
 * @package Module
 */

/**
 * @todo describe class
 * @package Module
 */
class NethGui_Module_LocalNetwork extends NethGui_Core_Module_TableController implements NethGui_Core_TopModuleInterface
{

    public function getParentMenuIdentifier()
    {
        return "Administration";
    }

    public function __construct()
    {

        $events = array();
        $columns = array('network', 'Mask', 'Router', 'SystemLocalNetwork', 'Actions');
        $dialogUpdate = new NethGui_Core_Module_TableDialog(
                'DlgLocalNetwork',
                'NethGui_View_LocalNetwork_Dialog',
                array(
                    array('network', self::VALID_IPv4, NULL),
                    array('Mask', self::VALID_IPv4, NULL),
                    array('Router', self::VALID_IP_OR_EMPTY, FALSE, NULL),
                ),
                array('create', 'update')
        );

        $dialogPrint = new NethGui_Core_Module_TableDialog(
                'DlgPrint',
                'NethGui_View_LocalNetwork_Print',
                array(
                    array('network', self::VALID_IPv4, NULL),
                    array('Mask', self::VALID_IPv4, NULL),
                    array('Router', self::VALID_IP_OR_EMPTY, FALSE, NULL),
                ),
                array('print')
        );

        parent::__construct('LocalNetwork', 'networks', 'network', $columns, array('create' => $dialogUpdate, 'update' => $dialogUpdate, 'print' => $dialogPrint), $events);
    }

    protected function processActionPrint(NethGui_Core_ConfigurationDatabase $db, $key, $values)
    {
        return TRUE;
    }

    protected function prepareColumnActions(NethGui_Core_ViewInterface $view, $mode, $values)
    {
        $columnView = parent::prepareColumnActions($view, $mode, $values);

        if (isset($values['SystemLocalNetwork']) && $values['SystemLocalNetwork'] == 'yes') {
            unset($columnView['update']);
            unset($columnView['delete']);
        }
        return $columnView;
    }

}

