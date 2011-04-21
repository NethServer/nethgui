<?php
/**
 * @package Core
 * @subpackage Table
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * @package Core
 * @subpackage Table
 */
class NethGui_Core_Table_ActionCreate extends NethGui_Core_Module_Action {
    public function __construct($identifier = NULL)
    {
        parent::__construct($identifier);
        $this->viewTemplate = 'NethGui_Core_View_ActionCreate';
    }
}
