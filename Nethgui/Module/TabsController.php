<?php
/**
 * Nethgui
 *
 * @package Module
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
 * @package Module
 */
class TabsController extends Nethgui\Core\Module\Controller
{

    public function renderDefault(Nethgui\Renderer\Abstract $view)
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
