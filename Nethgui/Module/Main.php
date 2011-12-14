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
//    private $finalModules;

    public function __construct($template, \Nethgui\Core\ModuleLoader $moduleLoader)
    {
        parent::__construct(FALSE, $template);
        $this->moduleLoader = $moduleLoader;
//        $this->finalModules = array();
    }

    public function bind(\Nethgui\Core\RequestInterface $request)
    {
        $idList = $request->getParameters();

        $this->currentModuleIdentifier = \Nethgui\array_head($request->getArguments());

        if ( ! in_array($this->currentModuleIdentifier, $idList)) {
            $idList[] = $this->currentModuleIdentifier;
        }

        foreach ($idList as $moduleIdentifier) {
            if (in_array($moduleIdentifier, array('Menu', 'Notification'))) {
                continue;
            }
            $this->addChild($this->moduleLoader->getModule($moduleIdentifier));
        }

        $menuModule = $this->moduleLoader->getModule('Menu');
        $menuModule->setModuleSet($this->moduleLoader)->setCurrentModuleIdentifier($this->currentModuleIdentifier);
        $this->addChild($menuModule);

        $notificationModule = $this->moduleLoader->getModule('Notification');
        $this->addChild($notificationModule);

        parent::bind($request);
    }

//    public function prepareFinalView(\Nethgui\Core\ViewInterface $view)
//    {
//        foreach ($this->finalModules as $child) {
//            $child->prepareView($view->spawnView($child, TRUE));
//        }
//    }

    public function prepareView(\Nethgui\Core\ViewInterface $view, $mode)
    {
//        foreach ($this->getChildren() as $child) {
//            if ($child instanceof \Nethgui\Core\CommandReceiverInterface) {
//                // postpone prepareView for command receivers:
//                $this->finalModules[] = $child;
//                continue;
//            }
//            $innerView = $view->spawnView($child, TRUE);
//            $child->prepareView($innerView, $mode);
//        }
        parent::prepareView($view, $mode);

        if ($view->getTargetFormat() !== $view::TARGET_XHTML) {
            return;
        }

        $pathUrl = $view->getPathUrl() . '/';

        $view['CurrentModule'] = $view[$this->currentModuleIdentifier];

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
