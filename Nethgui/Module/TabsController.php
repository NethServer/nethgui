<?php
/**
 * Nethgui
 *
 * @package Module
 */

/**
 * A Controller for handling a generic table CRUD scenario, and any other
 * action defined on a table.
 *
 * - Tracks the actions involving a row
 * - Tracks the actions involving the whole table
 *
 * @see Nethgui_Module_Table_Modify
 * @see Nethgui_Module_Table_Read
 * @package Module
 */
class Nethgui_Module_TabsController extends Nethgui_Core_Module_Controller
{

    public function renderDefault(Nethgui_Renderer_Abstract $view)
    {
        $container = $view->tabs()
            ->setAttribute('class', 'Tabs')
            ->setAttribute('tabClass', 'TabAction')
        ;

        foreach ($this->getChildren() as $index => $module) {
            $this->renderAction($view, $container, $module, $index);
        }

        return $container;
    }



}