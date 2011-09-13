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
            $lang = Nethgui_Framework::getInstance()->getLanguageCode();
            $immutables = array(
                'lang' => $lang,
                'cssMain' => base_url() . 'css/main.css',
                'js' => array(
                    'base' => base_url() . 'js/jquery-1.6.2.min.js',
                    'ui' => base_url() . 'js/jquery-ui-1.8.16.custom.min.js',
                    'dataTables' => base_url() . 'js/jquery.dataTables.min.js',
                    'test' => base_url() . 'js/nethgui.js',
                    'qTip' => base_url() . 'js/jquery.qtip.min.js',
                    'datepicker-regional' => base_url() . sprintf('js/jquery.ui.datepicker-%s.js', $lang),
                /* 'switcher' => 'http://jqueryui.com/themeroller/themeswitchertool/', */
                ),
            );

            foreach ($immutables as $immutableName => $immutableValue) {
                $view[$immutableName] = $immutableValue;
            }
        }

        foreach ($this->modules as $module) {
            $innerView = $view->spawnView($module, TRUE);
            $module->prepareView($innerView, $mode);
            // Consider the first module as Current.
            if ( ! isset($view['CurrentModule']) && $mode === self::VIEW_SERVER) {

                if ($module instanceof Nethgui_Core_Module_Abstract) {
                    if ( ! $module->hasInputForm()) {
                        $wrapView = $view->spawnView($module);
                        $wrapView['__originalView'] = $innerView;
                        $wrapView->setTemplate(array($this, 'renderFormWrap'));

                        $innerView = $wrapView;
                    }
                }

                $view['CurrentModule'] = $innerView;
            }
        }
    }

    public function renderFormWrap(Nethgui_Renderer_Abstract $view)
    {
        return $view->form()->insert($view->inset('__originalView'));
    }

    public function addModule(Nethgui_Core_ModuleInterface $module)
    {
        $this->modules[] = $module;
    }

}
