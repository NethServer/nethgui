<?php
namespace Nethgui\Module;

/*
 * Copyright (C) 2011 Nethesis S.r.l.
 * 
 * This script is part of NethServer.
 * 
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * World module.
 *
 * Puts modules into the World View for rendering.
 *
 */
class World extends \Nethgui\Core\Module\AbstractModule
{

    private $modules = array();

    public function prepareView(\Nethgui\Core\ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);

        $pathUrl = $view->getPathUrl() . '/';

        if ($mode === self::VIEW_SERVER) {
            $lang = $view->getTranslator()->getLanguageCode();
            $immutables = array(
                'lang' => $lang,
                'js' => new \ArrayObject(array(
                    'base' => $pathUrl . 'js/jquery-1.6.2.min.js',
                    'ui' => $pathUrl . 'js/jquery-ui-1.8.16.custom.min.js',
                    'dataTables' => $pathUrl . 'js/jquery.dataTables.min.js',
                    'test' => $pathUrl . 'js/nethgui.js',
                    'qTip' => $pathUrl . 'js/jquery.qtip.min.js',
                /* 'switcher' => 'http://jqueryui.com/themeroller/themeswitchertool/', */
                )),
                'favicon' => $pathUrl . 'images/favicon.ico',
                'css' => new \ArrayObject(array('0base' => $pathUrl . 'css/base.css')),
            );
            if ($lang != 'en') {
                $immutables['js']['datepicker-regional'] = $pathUrl . sprintf('js/jquery.ui.datepicker-%s.js', $lang);
            }

            foreach ($immutables as $immutableName => $immutableValue) {
                $view[$immutableName] = $immutableValue;
            }

            //read css from db
            $db = $this->getPlatform()->getDatabase('configuration');
            $customCss = $db->getProp('httpd-admin','css');
            $view['css']['1theme'] = $pathUrl .  ($customCss ? sprintf('css/%s.css', $customCss) : 'css/default.css');
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
                   $view['css']['2dashboard'] = $pathUrl . 'css/dashboard.css';
                   $view['js']['chart'] = $pathUrl . 'js/jquery.jqChart.min.js';
                   $view['js']['dashboard'] = $pathUrl . 'js/dashboard.js';
                }
            }
        }

    }

    public function addModule(\Nethgui\Core\ModuleInterface $module)
    {
        $this->modules[] = $module;
    }

}
