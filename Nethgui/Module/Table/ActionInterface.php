<?php
/**
 * @author Davide Principi <davide.principi@nethesis.it>
 */

namespace Nethgui\Module\Table;

/**
 * A table action is provided with an adapter to access the underlying database table
 *
 */
interface ActionInterface
{
    public function setTableAdapter(\Nethgui\Adapter\AdapterInterface $tableAdapter);
    public function hasTableAdapter();
}
