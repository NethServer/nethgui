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

        $F = Nethgui_Framework::getInstance();
        if ($mode === self::VIEW_SERVER) {
            $lang = $F->getLanguageCode();
            $immutables = array(
                'lang' => $lang,
                'js' => new ArrayObject(array(
                    'base' => $F->baseUrl() . 'js/jquery-1.6.2.min.js',
                    'ui' => $F->baseUrl() . 'js/jquery-ui-1.8.16.custom.min.js',
                    'dataTables' => $F->baseUrl() . 'js/jquery.dataTables.min.js',
                    'test' => $F->baseUrl() . 'js/nethgui.js',
                    'qTip' => $F->baseUrl() . 'js/jquery.qtip.min.js',
                /* 'switcher' => 'http://jqueryui.com/themeroller/themeswitchertool/', */
                )),
                'favicon' => $F->baseUrl() . 'images/favicon.ico',
                'css' => new ArrayObject(array('0base' => $F->baseUrl() . 'css/base.css')),
            );
            if ($lang != 'en') {
                $immutables['js']['datepicker-regional'] = $F->baseUrl() . sprintf('js/jquery.ui.datepicker-%s.js', $lang);
            }

            foreach ($immutables as $immutableName => $immutableValue) {
                $view[$immutableName] = $immutableValue;
            }

            //read css from db
            $db = $this->getHostConfiguration()->getDatabase('configuration');
            $view['css']['1theme'] = $db->getProp('httpd-admin','css') ? $F->baseUrl() . 'css/' . $db->getProp('httpd-admin','css') . ".css" : $F->baseUrl() . 'css/default.css';
            $view['company'] = $db->getProp('ldap','defaultCompany');
            $view['address'] = $db->getProp('ldap','defaultStreet').", ".$db->getProp('ldap','defaultCity');
        }

        foreach ($this->modules as $module) {
            $innerView = $view->spawnView($module, TRUE);
            $module->prepareView($innerView, $mode);
            // Consider the first module as Current.
            if ( ! isset($view['CurrentModule']) && $mode === self::VIEW_SERVER) {
                $view['CurrentModule'] = $innerView;
                if( $module->getIdentifier() == 'Status')
                {
                   $view['css']['2dashboard'] = $F->baseUrl() . 'css/dashboard.css'; 
                   $view['js']['chart'] = $F->baseUrl() . 'js/jquery.jqChart.min.js';
                   $view['js']['dashboard'] = $F->baseUrl() . 'js/dashboard.js';
                }
            }
        }

    }

    public function addModule(Nethgui_Core_ModuleInterface $module)
    {
        $this->modules[] = $module;
    }

}
