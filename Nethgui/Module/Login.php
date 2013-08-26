<?php
namespace Nethgui\Module;

/*
 * Copyright (C) 2012 Nethesis S.r.l.
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

use Nethgui\System\PlatformInterface as Valid;

/**
 * Perform user authentication through the PAM module
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class Login extends \Nethgui\Controller\AbstractController implements \Nethgui\Utility\SessionConsumerInterface
{
    /**
     *
     * @var \Nethgui\Utility\SessionInterface
     */
    private $session;

    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        $attributes = new SystemModuleAttributesProvider();
        $attributes->initializeFromModule($this);
        return $attributes;
    }

    public function initialize()
    {
        parent::initialize();

        $languages = array(
            'en' => 'English',
            'it' => 'Italiano'
        );

        $languageValidator = $this->createValidator()->memberOf(array_keys($languages));

        $this->declareParameter('username', Valid::NOTEMPTY);
        $this->declareParameter('password', Valid::NOTEMPTY);
        $this->declareParameter('language', $languageValidator, array($this, 'getDefaultLanguageCode'));
        $this->declareParameter('hostname', FALSE, array('configuration', 'SystemName'));
        $this->declareParameter('languageDatasource', FALSE, function () use ($languages) {
                return \Nethgui\Renderer\AbstractRenderer::hashToDatasource($languages);
            });
    }

    public function getDefaultLanguageCode()
    {
        return $this->getRequest()->getLanguageCode();
    }

    public function process()
    {
        $user = $this->getRequest()->getUser();
        if ( ! $user->isAuthenticated() && $this->getRequest()->isMutation()) {
            $user = new \Nethgui\Authorization\User($this->getPhpWrapper(), $this->getLog());
            $authenticated = $user->authenticate($this->parameters['username'], $this->parameters['password']);
            $user->setLanguageCode($this->parameters['language']);
            if ($authenticated) {
                $this->getLog()->notice(sprintf("%s: user %s logged in", __CLASS__, $this->parameters['username']));
                $this->session->login()->store(\Nethgui\Authorization\UserInterface::ID, $user);
            }
        }
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $view['path'] = '/Login/' . implode('/', $this->getRequest()->getPath());
        $user = $this->getRequest()->getUser();

        $view->setTemplate('Nethgui\Template\Login');
        $view->getCommandList('/Main')
            ->setDecoratorParameter('disableHeader', TRUE)
            ->setDecoratorParameter('disableMenu', TRUE)
//            ->setDecoratorParameter('disableFooter', TRUE)
        ;

        $isInvalidLoginRequest = ! $user->isAuthenticated()
            && $this->getRequest()->isMutation()
            && $this->getRequest()->isValidated();

        $isAuthenticatedUserLoggingInAgain = $user->isAuthenticated()
            && ! $this->getRequest()->isMutation();

        $isUnauthUserRequest = ! $user->isAuthenticated()
            && ! $this->getRequest()->isMutation()
            && count($this->getRequest()->getPath()) > 0;
        
        if ($isInvalidLoginRequest) {
            $view->getCommandList('/Notification')
                ->httpHeader('HTTP/1.1 400 Invalid credentials supplied')
                ->showMessage($view->translate('Invalid credentials'), \Nethgui\Module\Notification\AbstractNotification::NOTIFY_ERROR);
        } elseif ($isAuthenticatedUserLoggingInAgain) {
            $view->getCommandList()
                ->httpHeader('HTTP/1.1 302 Found')
                ->httpHeader('Location: ' . $view->getSiteUrl() . $view->getModuleUrl('/'));
        } elseif ($isUnauthUserRequest) {
            $view->getCommandList()
                ->httpHeader('HTTP/1.1 403 Forbidden');
        }
    }

    public function nextPath()
    {
        if ($this->getRequest()->isMutation() && $this->getRequest()->getUser()->isAuthenticated()) {
            $path = $this->getRequest()->getPath();
            if(count($path) === 0) {
                return '/';
            } else {
                return '/' . implode('/', $path) . sprintf(( $this->parameters['language'] !== $this->getRequest()->getLanguageCode() ? '?Language[switch]=%s' : ''), $this->parameters['language']);
            }            
        }
        return FALSE;
    }

    public function setSession(\Nethgui\Utility\SessionInterface $session)
    {
        $this->session = $session;
        return $this;
    }

}