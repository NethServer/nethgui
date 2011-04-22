<?php
/**
 * @package Core
 * @subpackage Module
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * Execution of an application task
 *
 * @package Core
 * @subpackage Module
 */
class NethGui_Core_Module_Action extends NethGui_Core_Module_Standard {

    private $arguments = array();
    private $actionName;

    /**
     * Assign arguments to action invocation.
     *
     * Arguments come from URL segments and QUERY vars.
     *
     * @param array $arguments An ordered array of arguments to this Action
     */
    public function bindArguments($actionName, $arguments) {
        $this->actionName = $actionName;
        $this->arguments = $arguments;
    }

    /**
     * Obtain the arguments to the invocation of this Action.
     *
     * @see bindArguments()
     * @return array
     */
    public function getArguments() {
        return $this->arguments;
    }

    public function getActionName() {
        return $this->actionName;
    }

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        $view['enabled'] = TRUE;
        $view['action'] = $this->actionName;
        parent::prepareView($view, $mode);
    }
}
