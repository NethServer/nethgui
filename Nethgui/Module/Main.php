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
class Main extends \Nethgui\Core\Module\ListComposite implements \Nethgui\Command\CommandReceiverInterface
{

    /**
     * @var \Nethgui\Core\ModuleLoader
     */
    private $moduleLoader;
    private $currentModuleIdentifier;

    /**
     * The template applied to the Main view
     * @see ViewInterface#setTemplate()
     * @var mixed
     */
    private $template;

    public function __construct($template, \Nethgui\Core\ModuleLoader $moduleLoader, $fileNameResolver)
    {
        parent::__construct(FALSE);
        $this->template = $template;
        $this->moduleLoader = $moduleLoader;
        $this->fileNameResolver = $fileNameResolver;
    }

    public function bind(\Nethgui\Core\RequestInterface $request)
    {
        $idList = $request->getParameterNames();

        $this->currentModuleIdentifier = \Nethgui\array_head($request->getPath());

        if ( ! in_array($this->currentModuleIdentifier, $idList)) {
            $idList[] = $this->currentModuleIdentifier;
        }

        $systemModules = array('Menu', 'Notification', 'Resource');

        try {
            foreach ($idList as $moduleIdentifier) {
                if (in_array($moduleIdentifier, $systemModules)) {
                    continue;
                }
                $this->addChild($this->moduleLoader->getModule($moduleIdentifier));
            }
        } catch (\RuntimeException $ex) {
            throw new \Nethgui\Exception\HttpException('Not found', 404, 1324379722, $ex);
        }

        $menuModule = $this->moduleLoader->getModule('Menu');
        $menuModule->setModuleSet($this->moduleLoader)->setCurrentModuleIdentifier($this->currentModuleIdentifier);
        $this->addChild($menuModule);

        $this->addChild($this->moduleLoader->getModule('Notification'));
        $this->addChild($this->moduleLoader->getModule('Resource'));

        if ($this->currentModuleIdentifier === 'Help') {
            $this->moduleLoader->getModule('Help')->setModuleSet($this->moduleLoader)->setFileNameResolver($this->fileNameResolver);
        }

        parent::bind($request);
    }

    public function prepareView(\Nethgui\Core\ViewInterface $view)
    {
        $this->view = $view;

        parent::prepareView($view);
        $view->setTemplate($this->template);

        if ($view->getTargetFormat() !== 'xhtml') {
            return;
        }

        $view['currentModule'] = $this->currentModuleIdentifier;
        $view['lang'] = $view->getTranslator()->getLanguageCode();

        //read css from db
        $db = $this->getPlatform()->getDatabase('configuration');
//        $customCss = $db->getProp('httpd-admin', 'css');
//        $view['css']['1theme'] = $pathUrl . ($customCss ? sprintf('css/%s.css', $customCss) : 'css/default.css');
        $view['company'] = $db->getProp('ldap', 'defaultCompany');
        $view['address'] = $db->getProp('ldap', 'defaultStreet') . ", " . $db->getProp('ldap', 'defaultCity');
    }

    /**
     * Available commands:
     * - setDecoratorTemplate ( string|callable $template )
     *
     * @param \Nethgui\Core\ViewInterface $origin
     * @param type $selector
     * @param type $name
     * @param type $arguments
     */
    public function executeCommand(\Nethgui\Core\ViewInterface $origin, $selector, $name, $arguments)
    {
        if ($name === 'setDecoratorTemplate'
            && isset($arguments[0])
            && $this->view instanceof \Nethgui\Core\ViewInterface) {
            $this->view->setTemplate($arguments[0]);
        }
    }

}