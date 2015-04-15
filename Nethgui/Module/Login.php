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
class Login extends \Nethgui\Controller\AbstractController implements \Nethgui\Utility\SessionConsumerInterface, \Nethgui\Component\DependencyConsumer
{
    /**
     *
     * @var \Nethgui\Utility\SessionInterface
     */
    private $session;

    /**
     *
     * @var \Nethgui\Utility\HttpResponse
     */
    private $httpResponse;

    /**
     *
     * @var \Nethgui\Model\UserNotifications
     */
    private $userNotifications;

    /**
     *
     * @var \ArrayAccess
     */
    private $xhtmlDecoratorParams;

    private $forcedRedirect;

    private $languages = array();

    /**
     *
     * @var \Nethgui\System\ValidatorInterface
     */
    private $loginValidator;

    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        $attributes = new SystemModuleAttributesProvider();
        $attributes->initializeFromModule($this);
        return $attributes;
    }

    private function getLocales()
    {
        static $locales;
        if(isset($locales)) {
            return $locales;
        }

        $output = array(); $retval = 0;
        $this->getPhpWrapper()->exec('/usr/bin/locale -a', $output, $retval);

        $locales = array();
        foreach($output as $line) {
            $M = array();

            if(preg_match('/^(?P<lang>[a-z][a-z])_(?P<region>[A-Z][A-Z])\.utf8$/', trim($line), $M) && in_array($M['lang'], $this->languages)) {
                $locales[] = $M['lang'] . '-' . $M['region'];
            }
        }

        return $locales;
    }

    public function initialize()
    {
        parent::initialize();

        $localeValidator = $this->createValidator()->memberOf($this->getLocales());

        $this->declareParameter('username', Valid::NOTEMPTY);
        $this->declareParameter('password', Valid::NOTEMPTY);
        $this->declareParameter('path', Valid::NOTEMPTY);
        $this->declareParameter('language', $localeValidator, array($this, 'getLocaleFromRequest'));
        $this->declareParameter('hostname', FALSE, array('configuration', 'SystemName'));

    }

    public function bind(\Nethgui\Controller\RequestInterface $request) {
        parent::bind($request);
        if($this->forcedRedirect) {
            $this->parameters['path'] = '/' . $this->forcedRedirect;
        }
    }

    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        parent::validate($report);

        if($report->hasValidationErrors()) {
            return;
        }

        /* @var $user \Nethgui\Authorization\User */
        $user = $this->getRequest()->getUser();
        if ( ! $user->isAuthenticated() && $this->getRequest()->isMutation()) {
            $authenticated = $user->authenticate($this->parameters['username'], $this->parameters['password']);
            $user->setLocale($this->parameters['language']);
            if( ! $authenticated) {
                $report->addValidationError($this, 'password', $this->loginValidator);
            }
        }
    }

    public function getLocaleFromRequest()
    {
        $locale = $this->getRequest()->getLocale();

        // FIXME: this mapping is provided for backward compatibility
        // and can be removed in future versions:
        if($locale === 'en') {
            $locale = 'en-GB';
        } elseif($locale === 'it') {
            $locale = 'it-IT';
        }
        return $locale;
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $user = $this->getRequest()->getUser();

        $view->setTemplate('Nethgui\Template\Login');

        $tmp = array();
        foreach ($this->getLocales() as $l) {
            $lang = substr($l, 0, 2);;
            if (\extension_loaded('intl')) {
                $tmp[\locale_get_display_language($lang)][$l] = sprintf('%s (%s)', \locale_get_display_language($l, $lang), \locale_get_display_region($l, $lang));
            } else {
                $tmp[$lang][$l] = $l;
            }
        }
        $view['languageDatasource'] = \Nethgui\Renderer\AbstractRenderer::hashToDatasource($tmp, TRUE);

        $this->xhtmlDecoratorParams['disableHeader'] = TRUE;
        $this->xhtmlDecoratorParams['disableMenu'] = TRUE;
        $this->xhtmlDecoratorParams['disableFooter'] = FALSE;

        $isAuthenticatedUserLoggingInAgain = $user->isAuthenticated() && ! $this->getRequest()->isMutation();

        $isUnauthUserRequest = ! $user->isAuthenticated() && ! $this->getRequest()->isMutation() && $this->parameters['path'];

        if ($isAuthenticatedUserLoggingInAgain) {
            $this->httpResponse
                ->setStatus(302)
                ->addHeader('Location: ' . $view->getSiteUrl() . $view->getModuleUrl('/'))
            ;
        } elseif ($isUnauthUserRequest) {
            $this->httpResponse->setStatus(403, 'Forbidden');
        }
    }

    public function nextPath()
    {
        if ($this->getRequest()->isMutation() && $this->getRequest()->getUser()->isAuthenticated()) {
            if ( ! $this->parameters['path']) {
                return '/';
            } else {
                return $this->parameters['path'] . sprintf(( $this->parameters['language'] !== $this->getRequest()->getLocale() ? '?Language[switch]=%s' : ''), $this->parameters['language']);
            }
        }
        return FALSE;
    }

    public function setSession(\Nethgui\Utility\SessionInterface $session)
    {
        $this->session = $session;
        return $this;
    }

    public function getDependencySetters()
    {
        $myHttpResponse = &$this->httpResponse;
        $myUserNotifications = &$this->userNotifications;
        $myXhtmlDecoratorParams = &$this->xhtmlDecoratorParams;
        $myForcedRedirect = &$this->forcedRedirect;
        $loginValidator = &$this->loginValidator;
        $languages = &$this->languages;
        return array(
            'HttpResponse' => function (\Nethgui\Utility\HttpResponse $r) use (&$myHttpResponse) {
            $myHttpResponse = $r;
        },
            'UserNotifications' => function(\Nethgui\Model\UserNotifications $n) use (&$myUserNotifications) {
            $myUserNotifications = $n;
        },
            'decorator.xhtml.params' => function(\ArrayAccess $params) use (&$myXhtmlDecoratorParams) {
            $myXhtmlDecoratorParams = $params;
        },
            'login.forced_redirect' => function($id) use (&$myForcedRedirect) {
            $myForcedRedirect = $id;
        },
           'user.authenticate' => function(\Nethgui\System\ValidatorInterface $v) use (&$loginValidator) {
            $loginValidator = $v;
        },
            'l10n.available_languages' => function ($langs) use (&$languages) {
            $languages = $langs;
        },
        );
    }

}