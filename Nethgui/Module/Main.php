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

    public function __construct($template, \Nethgui\Core\ModuleLoader $moduleLoader)
    {
        parent::__construct(FALSE, $template);
        $this->moduleLoader = $moduleLoader;
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

        parent::bind($request);
    }

    public function prepareView(\Nethgui\Core\ViewInterface $view, $mode)
    {

        parent::prepareView($view, $mode);

        /*
         * Stylesheets and script files can be served only by the Resource module
         */
        if ($view->getTargetFormat() === 'css'
            || $view->getTargetFormat() === 'js') {
            $view->setTemplate(function(\Nethgui\Renderer\TemplateRenderer $renderer) use ($view) {
                    return $renderer->spawnRenderer($view['Resource'])->render();
                });
        }


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

}