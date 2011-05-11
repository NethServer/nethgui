<?php
/**
 * NethGui
 *
 * @package Module
 */

/**
 * World module.
 *
 * This is the root of the modules composition.
 *
 * @package Module
 */
class NethGui_Module_World extends NethGui_Core_Module_Abstract
{

    private $modules = array();

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        
        if ($mode === self::VIEW_REFRESH) {
            $immutables = array(
                'lang' => NethGui_Framework::getInstance()->getLanguageCode(),
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
        }

        foreach ($this->modules as $module) {
            $innerView = $view->spawnView($module, TRUE);
            $module->prepareView($innerView, $mode);
            // Consider the first module as CURRENT.
            if ( ! isset($view['CurrentModule']) && $mode === self::VIEW_REFRESH) {
                $view['CurrentModule'] = $innerView;
            }
        }
    }

    public function addModule(NethGui_Core_ModuleInterface $module)
    {
        $this->modules[] = $module;
    }

}
