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
 * This is the root of the module composition
 *
 * - Enforce basic module authorization
 * - Prepare the decoration view
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class Main extends \Nethgui\Controller\ListComposite implements \Nethgui\View\CommandReceiverInterface
{
    /**
     * @var \Nethgui\Module\ModuleSetInterface
     */
    private $modules;
    private $currentModuleIdentifier;

    /**
     * The template applied to the Main view
     * @see ViewInterface#setTemplate()
     * @var mixed
     */
    private $template;

    /**
     *
     * @var \Nethgui\View\ViewInterface
     */
    private $view;

    /**
     * Parameters injected into the decorator view
     * @var array
     */
    private $decoratorParameter;

    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        $attributes = new SystemModuleAttributesProvider();
        $attributes->initializeFromModule($this);
        return $attributes;
    }

    public function __construct($template, \Nethgui\Module\ModuleSetInterface $modules)
    {
        parent::__construct(FALSE);
        $this->template = $template;
        $this->decoratorParameter = array();
        $this->modules = $modules;
    }

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        $idList = array_filter($request->getParameterNames(), function($p) use ($request) {
            return is_array($request->getParameter($p));
        });
        $this->currentModuleIdentifier = \Nethgui\array_head($request->getPath());
       
        try {
            foreach ($idList as $moduleIdentifier) {
                $moduleInstance = $this->modules->getModule($moduleIdentifier);
                $this->addChild($moduleInstance);
            }
        } catch (\Nethgui\Exception\AuthorizationException $ex) {
            throw $ex;
        } catch (\RuntimeException $ex) {
            throw new \Nethgui\Exception\HttpException('Not found', 404, 1324379722, $ex);
        }

        $this->authorize($request);

        parent::bind($request);
    }

    private function authorize(\Nethgui\Controller\RequestInterface $request)
    {
        foreach ($this->getChildren() as $child) {
            if ($request->isMutation()) {
                $auth = $this->getPolicyDecisionPoint()->authorize($request->getUser(), $child, self::ACTION_MUTATE);
            } else {
                $auth = $this->getPolicyDecisionPoint()->authorize($request->getUser(), $child, self::ACTION_QUERY);
            }

            if ($auth->isDenied()) {
                throw $auth->asException(1327499272);
            }
        }
    }

    /**
     * @return \Nethgui\Module\ModuleInterface
     */
    private function getCurrentModule()
    {
        return $this->modules->getModule($this->currentModuleIdentifier);
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        $this->view = $view;

        parent::prepareView($view);
        $view->setTemplate(array($this, 'renderDecorated'));

        if ($view->getTargetFormat() === 'xhtml') {
            $this->prepareViewXhtml($view);
        }
    }

    private function prepareViewXhtml(\Nethgui\View\ViewInterface $view)
    {
        $currentModule = $this->getCurrentModule();

        $view['currentModule'] = $this->currentModuleIdentifier;
        $view['lang'] = $view->getTranslator()->getLanguageCode();

        //read css from db
        $db = $this->getPlatform()->getDatabase('configuration');
        $colors = $db->getProp('httpd-admin', 'colors');
        if ($colors) {
            $colors = explode(',',$colors);
            $view['colors'] = $colors;
        }  
        $logo = $db->getProp('httpd-admin', 'logo');
        $view['logo'] = $view->getPathUrl() . ($logo ? sprintf('images/%s', $logo) : 'images/logo.png') ;
        $view['company'] = $db->getProp('OrganizationContact', 'Company');
        $view['address'] = $db->getProp('OrganizationContact', 'Street') . ", " . $db->getProp('OrganizationContact', 'City');
        $favicon = $db->getProp('httpd-admin', 'favicon');
        $view['favicon'] = $view->getPathUrl() . ($favicon ? sprintf('images/%s', $favicon) : 'images/favicon.png') ;
        $view['moduleTitle'] = $view->getTranslator()->translate($currentModule, $currentModule->getAttributesProvider()->getTitle());

        $view['currentModuleOutput'] = 'currentModuleOutput';
        $view['menuOutput'] = 'menuOutput';
        $view['helpAreaOutput'] = 'helpAreaOutput';
        $view['notificationOutput'] = 'notificationOutput';
        $view['logoutOutput'] = 'logoutOutput';

    }

    /**
     * Available commands:
     * - setDecoratorTemplate ( string|callable $template )
     *
     * @param \Nethgui\View\ViewInterface $origin
     * @param type $selector
     * @param type $name
     * @param type $arguments
     */
    public function executeCommand(\Nethgui\View\ViewInterface $origin, $selector, $name, $arguments)
    {
        if ($name === 'setDecoratorTemplate'
            && isset($arguments[0])) {
            $this->template = $arguments[0];
        } elseif ($name === 'setDecoratorParameter') {
            $this->decoratorParameter[$arguments[0]] = $arguments[1];
        }
    }

    public function renderDecorated(\Nethgui\Renderer\TemplateRenderer $renderer)
    {
        $decoratorView = $this->view->spawnView($this->getCurrentModule(), FALSE)
            ->copyFrom($this->decoratorParameter)
            ->copyFrom($this->view)
            ->setTemplate($this->template);

        if ( ! $renderer instanceof \Nethgui\Renderer\Xhtml) {
            return $renderer->spawnRenderer($decoratorView)->render();
        }

        // Override helpAreaOutput
        $decoratorView['helpAreaOutput'] = (String) $renderer->panel($renderer::STATE_UNOBSTRUSIVE)
                ->setAttribute('class', 'HelpArea')
                ->insert(
                    $renderer->panel()
                    ->setAttribute('class', 'wrap')
                    ->insert(
                        $renderer->buttonList($renderer::BUTTONSET)->insert($renderer->button('Hide', $renderer::BUTTON_CANCEL))
                    )
        );

        // Override currentModuleOutput
        // - We must render CurrentModule before NotificationArea to catch notifications
        if ($this->getCurrentModule() instanceof \Nethgui\Module\ModuleCompositeInterface) {
            $decoratorView['currentModuleOutput'] = (String) $renderer->inset($this->currentModuleIdentifier);
        } else {
            $decoratorView['currentModuleOutput'] = (String) $renderer->panel()->setAttribute('class', 'Controller')
                    ->insert($renderer->inset($renderer['currentModule'], $renderer::INSET_FORM | $renderer::INSET_WRAP)->setAttribute('class', 'Action'));
        }

        // Override notificationOutput
        $decoratorView['notificationOutput'] = (String) $renderer->inset('Notification');

        // Override menuOutput
        $decoratorView['menuOutput'] = (String) $renderer->inset('Menu');
        $decoratorView['logoutOutput'] = (String) $renderer->inset('Logout');

        // Set default partial view visibility:
        if( ! isset($decoratorView['disableHeader'])) {
            $decoratorView['disableHeader'] = FALSE;
        }
        if( ! isset($decoratorView['disableMenu'])) {
            $decoratorView['disableMenu'] = FALSE;
        }
        if( ! isset($decoratorView['disableFooter'])) {
            $decoratorView['disableFooter'] = TRUE;
        }

        return $renderer->spawnRenderer($decoratorView)->render();
    }

    public function nextPath()
    {
        foreach ($this->getChildren() as $child) {
            if ($child instanceof \Nethgui\Controller\RequestHandlerInterface
                && $child->getIdentifier() === $this->currentModuleIdentifier) {
                return $child->nextPath();
            }
        }
        return FALSE;
    }

}
