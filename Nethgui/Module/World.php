<?php
/**
 * @package Module
 */

namespace Nethgui\Module;

/**
 * World module.
 *
 * Puts modules into the World View for rendering.
 *
 * @package Module
 */
class World extends \Nethgui\Core\Module\AbstractModule
{

    private $modules = array();

    public function prepareView(\Nethgui\Core\ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        
        if ($mode === self::VIEW_SERVER) {
            $lang = $view->getTranslator()->getLanguageCode();
            $immutables = array(
                'lang' => $lang,
                'js' => new ArrayObject(array(
                    'base' => NETHGUI_BASEURL . 'js/jquery-1.6.2.min.js',
                    'ui' => NETHGUI_BASEURL . 'js/jquery-ui-1.8.16.custom.min.js',
                    'dataTables' => NETHGUI_BASEURL . 'js/jquery.dataTables.min.js',
                    'test' => NETHGUI_BASEURL . 'js/nethgui.js',
                    'qTip' => NETHGUI_BASEURL . 'js/jquery.qtip.min.js',
                /* 'switcher' => 'http://jqueryui.com/themeroller/themeswitchertool/', */
                )),
                'favicon' => NETHGUI_BASEURL . 'images/favicon.ico',
                'css' => new ArrayObject(array('0base' => NETHGUI_BASEURL . 'css/base.css')),
            );
            if ($lang != 'en') {
                $immutables['js']['datepicker-regional'] = NETHGUI_BASEURL . sprintf('js/jquery.ui.datepicker-%s.js', $lang);
            }

            foreach ($immutables as $immutableName => $immutableValue) {
                $view[$immutableName] = $immutableValue;
            }

            //read css from db
            $db = $this->getPlatform()->getDatabase('configuration');
            $customCss = NETHGUI_CUSTOMCSS !== FALSE ? strval(NETHGUI_CUSTOMCSS) : $db->getProp('httpd-admin','css');
            $view['css']['1theme'] = NETHGUI_BASEURL .  ($customCss ? sprintf('css/%s.css', $customCss) : 'css/default.css');
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
                   $view['css']['2dashboard'] = NETHGUI_BASEURL . 'css/dashboard.css';
                   $view['js']['chart'] = NETHGUI_BASEURL . 'js/jquery.jqChart.min.js';
                   $view['js']['dashboard'] = NETHGUI_BASEURL . 'js/dashboard.js';
                }
            }
        }

    }

    public function addModule(\Nethgui\Core\ModuleInterface $module)
    {
        $this->modules[] = $module;
    }

}
