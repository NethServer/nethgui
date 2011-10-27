<?php
/**
 * @package Module
 * @subpackage Table
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * A table action is provided with an adapter to access the underlying database table
 *
 * @package Module
 * @subpackage Table
 */
interface Nethgui_Module_Table_ActionInterface
{
    public function setTableAdapter(Nethgui_Adapter_AdapterInterface $tableAdapter);
    public function hasTableAdapter();
}