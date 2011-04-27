<?php
/**
 * NethGui
 *
 * @package Core
 * @subpackage Module
 */

/**
 * World module.
 *
 * This is the root of the modules composition.
 *
 * @package Core
 * @subpackage Module
 */
class NethGui_Core_Module_World extends NethGui_Core_Module_Abstract
{

    private $modules = array();

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        $immutables = array(
            'cssMain' => base_url() . 'css/main.css',
            'js' => array(
                'base' => base_url() . 'js/jquery-1.5.1.min.js',
                'ui' => base_url() . 'js/jquery-ui-1.8.10.custom.min.js',
                'test' => base_url() . 'js/nethgui.js',
            ),
        );

        foreach ($immutables as $immutableName => $immutableValue) {
            $view[$immutableName] = $immutableValue;
        }

        parent::prepareView($view, $mode);

        foreach ($this->modules as $module) {
            $view[$module->getIdentifier()] = $view->spawnView($module);
            $module->prepareView($view[$module->getIdentifier()], $mode);

            if(!isset($view['currentModule'])) {
                $view['currentModule'] = $view[$module->getIdentifier()];
            }
        }
    }

    public function addModule(NethGui_Core_ModuleInterface $module)
    {
        $this->modules[] = $module;
    }

}
