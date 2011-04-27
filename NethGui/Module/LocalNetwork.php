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
        $columns = array(
            'network',
            'Mask',
            'Router',
            'SystemLocalNetwork',
            'Actions'
        );

        $actions = array(
            array('create', 'NethGui_View_LocalNetwork_CreateUpdate', TRUE),
            array('update', 'NethGui_View_LocalNetwork_CreateUpdate', NULL),
            array('delete', 'NethGui_Core_View_TableDelete', NULL),
        );

        $parameterSchema = array(
            array('network', self::VALID_IPv4),
            array('Mask', self::VALID_IPv4),
            array('Router', self::VALID_IPv4_OR_EMPTY),
        );

        parent::__construct('LocalNetwork', 'networks', 'network', $parameterSchema, $columns, $actions);
        $this->viewTemplate = 'NethGui_Core_View_TableController';
    }

    /**
     *
     * @param NethGui_Core_Module_TableRead $action
     * @param NethGui_Core_ViewInterface $view
     * @param int $mode
     * @param array $values
     * @return string|NethGui_Core_ViewInterface
     */
     public function prepareViewForColumnActions(NethGui_Core_Module_TableRead $action, NethGui_Core_ViewInterface $view, $mode, $values) {

         if(isset($values['SystemLocalNetwork']) &&  $values['SystemLocalNetwork'] == 'yes') {
            return '';
         }

         return $action->prepareViewForColumnActions($view, $mode, $values);
     }
}

