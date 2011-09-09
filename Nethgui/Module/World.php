<?php
/**
 * @package Module
 */

/**
 * World module.
 *
 * Puts modules into the World View for rendering.
 *
 * @package Module
 */
class Nethgui_Module_World extends Nethgui_Core_Module_Abstract
{

    private $modules = array();

    public function prepareView(Nethgui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        
        if ($mode === self::VIEW_SERVER) {
            $immutables = array(
                'lang' => Nethgui_Framework::getInstance()->getLanguageCode(),
                'cssMain' => base_url() . 'css/main.css',
                'js' => array(
                    'base' => base_url() . 'js/jquery-1.6.2.min.js',
                    'ui' => base_url() . 'js/jquery-ui-1.8.16.custom.min.js',
                    'dataTables' => base_url() . 'js/jquery.dataTables.min.js',
                    'test' => base_url() . 'js/nethgui.js',
                    /*'switcher' => 'http://jqueryui.com/themeroller/themeswitchertool/',*/ 
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
            if ( ! isset($view['CurrentModule']) && $mode === self::VIEW_SERVER) {
                $view['CurrentModule'] = $innerView;
            }
        }
    }

    public function addModule(Nethgui_Core_ModuleInterface $module)
    {
        $this->modules[] = $module;
    }

}
