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
 * TODO: add component description here
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class Main extends \Nethgui\Core\Module\ListComposite
{

    /**
     * @var \Nethgui\Core\ModuleLoader
     */
    private $moduleLoader;
    private $currentModuleIdentifier;

    /**
     *
     * @var \Nethgui\Core\NotificationManager
     */
    private $notificationManager;

    public function __construct($template, \Nethgui\Core\ModuleLoader $moduleLoader, \Nethgui\Core\NotificationManager $notificationManager)
    {
        parent::__construct(FALSE, $template);
        $this->moduleLoader = $moduleLoader;
        $this->notificationManager = $notificationManager;
    }

    public function bind(\Nethgui\Core\RequestInterface $request)
    {
        $idList = $request->getParameters();

        $this->currentModuleIdentifier = \Nethgui\array_head($request->getArguments());

        if ( ! in_array($this->currentModuleIdentifier, $idList)) {
            $idList[] = $this->currentModuleIdentifier;
        }

        foreach ($idList as $moduleIdentifier) {
            $this->addChild($this->moduleLoader->getModule($moduleIdentifier));
        }

        $menuModule = $this->moduleLoader->getModule('Menu');
        $menuModule->setModuleSet($this->moduleLoader)->setCurrentModuleIdentifier($this->currentModuleIdentifier);

        if ( ! in_array('Menu', $idList)) {
            $this->addChild($menuModule);
        }

        $notificationArea = $this->moduleLoader->getModule('NotificationArea');
        $notificationArea->setNotificationManager($this->notificationManager);
        
        if ( ! in_array('NotificationArea', $idList)) {
            $this->addChild($notificationArea);
        }

        parent::bind($request);
    }

    public function prepareView(\Nethgui\Core\ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);

        if ($mode !== self::VIEW_SERVER) {
            return;
        }
        
        $pathUrl = $view->getPathUrl() . '/';

        $view['CurrentModule'] = $view[$this->currentModuleIdentifier];
        //$view->setTemplate(array($this, 'renderWorldDecoration'));

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
        $customCss = $db->getProp('httpd-admin', 'css');
        $view['css']['1theme'] = $pathUrl . ($customCss ? sprintf('css/%s.css', $customCss) : 'css/default.css');
        $view['company'] = $db->getProp('ldap', 'defaultCompany');
        $view['address'] = $db->getProp('ldap', 'defaultStreet') . ", " . $db->getProp('ldap', 'defaultCity');
    }

}
