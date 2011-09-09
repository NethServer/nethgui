<?php
/**
 * @package Module
 * @subpackage Table
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * A Table Action receives a TableAdapter to modify a table
 * It specifies the dialog rendering style.
 *
 * @package Module
 * @subpackage Table
 * 
 */
class Nethgui_Module_Table_Action extends Nethgui_Core_Module_Standard
{
    /**
     *
     * @var Nethgui_Adapter_AdapterInterface
     */
    protected $tableAdapter;
      
    public function setTableAdapter(Nethgui_Adapter_AdapterInterface $tableAdapter)
    {
        if ( ! $this->hasTableAdapter())
        {
            $this->tableAdapter = $tableAdapter;
        }
    }

    public function hasTableAdapter()
    {
        return ! is_null($this->tableAdapter);
    }

    public function isModal()
    {
        return FALSE;
    }
    
}

