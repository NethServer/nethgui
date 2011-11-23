<?php
/**
 * @package Module
 * @subpackage Table
 * @author Davide Principi <davide.principi@nethesis.it>
 */

namespace Nethgui\Module\Table;

/**
 * A table action is provided with an adapter to access the underlying database table
 *
 * @package Module
 * @subpackage Table
 */
interface ActionInterface
{
    public function setTableAdapter(\Nethgui\Adapter\AdapterInterface $tableAdapter);
    public function hasTableAdapter();
}
