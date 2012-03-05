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
class Main extends \Nethgui\Controller\ListComposite implements \Nethgui\View\CommandReceiverInterface, \Nethgui\Authorization\PolicyEnforcementPointInterface
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

    /**
     *
     * @var \Nethgui\Authorization\PolicyDecisionPointInterface
     */
    private $pdp;

    /**
     *
     * @var array
     */
    private $systemModules;

    public function __construct($template, \Nethgui\Module\ModuleSetInterface $modules)
    {
        parent::__construct(FALSE);
        $this->template = $template;
        $this->decoratorParameter = array();
        $this->modules = $modules;
        $this->systemModules = array('Menu', 'Notification', 'Resource', 'Logout');
    }

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        $idList = array_merge($request->getParameterNames(), $this->systemModules);
        $this->currentModuleIdentifier = \Nethgui\array_head($request->getPath());
        $idList[] = $this->currentModuleIdentifier;

        $idList = array_unique($idList);

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
                $auth = $this->pdp->authorize($request->getUser(), $child, self::ACTION_MUTATE);
            } else {
                $auth = $this->pdp->authorize($request->getUser(), $child, self::ACTION_QUERY);
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
//        $customCss = $db->getProp('httpd-admin', 'css');
//        $view['css']['1theme'] = $pathUrl . ($customCss ? sprintf('css/%s.css', $customCss) : 'css/default.css');
        $view['company'] = $db->getProp('ldap', 'defaultCompany');
        $view['address'] = $db->getProp('ldap', 'defaultStreet') . ", " . $db->getProp('ldap', 'defaultCity');
        $view['favicon'] = $view->getPathUrl() . 'images/favicon.ico';
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

        // require global javascript resources:
        $renderer->useFile('js/jquery-1.6.2.min.js')
            ->useFile('js/jquery-ui-1.8.16.custom.min.js') //->useFile('js/jquery-ui.js')
            ->useFile('js/jquery.dataTables.min.js')
            ->useFile('js/jquery.qtip.min.js')
            ->useFile(sprintf('js/jquery.ui.datepicker-%s.js', $decoratorView['lang']))
            ->includeFile('Nethgui/Js/jquery.nethgui.base.js')
            ->includeFile('Nethgui/Js/jquery.nethgui.loading.js')
            ->includeFile('Nethgui/Js/jquery.nethgui.helparea.js')
        ;


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

    public function setPolicyDecisionPoint(\Nethgui\Authorization\PolicyDecisionPointInterface $pdp)
    {
        $this->pdp = $pdp;
        return $this;
    }

}