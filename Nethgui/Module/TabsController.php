<?php
/**
 * Nethgui
 *
 */

namespace Nethgui\Module;

/**
 * A Controller for handling a generic table CRUD scenario, and any other
 * action defined on a table.
 *
 * - Tracks the actions involving a row
 * - Tracks the actions involving the whole table
 *
 * @see Table\Modify
 * @see Table\Read
 */
class TabsController extends \Nethgui\Core\Module\Controller
{

    public function renderDefault(\Nethgui\Renderer\Xhtml $view)
    {
        $container = $view->tabs()
            ->setAttribute('class', 'TabsController')
            ->setAttribute('tabClass', 'TabAction')
        ;

        foreach ($this->getChildren() as $index => $module) {
            $container->insert($view->inset($module->getIdentifier()));
        }

        return $container;
    }

}
