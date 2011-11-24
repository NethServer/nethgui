<?php
/**
 * @author Davide Principi <davide.principi@nethesis.it>
 */

namespace Nethgui\Module\Table;

/**
 * A Table Action receives a TableAdapter to modify a table
 * It specifies the dialog rendering style.
 *
 * 
 */
class Action extends \Nethgui\Core\Module\Standard implements ActionInterface, \Nethgui\Core\Module\DefaultUiStateInterface
{
    /**
     *
     * @var \Nethgui\Adapter\AdapterInterface
     */
    protected $tableAdapter;
      
    public function setTableAdapter(\Nethgui\Adapter\AdapterInterface $tableAdapter)
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

    public function getDefaultUiStyleFlags()
    {
        switch($this->getIdentifier()) {
            case 'delete':
                return self::STYLE_DIALOG;
            case 'read':
                return self::STYLE_ENABLED;
        }
        return 0;
    }
}

