<?php

namespace Nethgui\View;

/*
 * Copyright (C) 2014  Nethesis S.r.l.
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
 * @since 1.6
 */
class LegacyCommandBag extends \ArrayObject
{
    /**
     *
     * @var \Nethgui\View\View
     */
    private $view;
    private $currentSelector, $currentOrigin;

    /**
     *
     * @var \Nethgui\Controller\ResponseInterface
     */
    private $response;

    public function __construct(\Nethgui\View\View $view, \Nethgui\Controller\ResponseInterface $response)
    {
        parent::__construct(array());
        $this->view = $view;
        $this->response = $response;
    }

    public function setContext($origin, $selector)
    {
        $this->currentOrigin = $origin;
        $this->currentSelector = $selector;
        return $this;
    }

    public function __call($name, $arguments)
    {
        $this->getLog()->deprecated();
        $receiver = $this->currentOrigin->getUniqueId($this->currentSelector);
        $argsArray = $this->prepareArguments($this->view, $arguments);

        $this[] = array(
            'R' => $receiver,
            'M' => $name,
            'A' => $argsArray,
        );

        return $this;
    }

    public function getLog()
    {
        return $this->view->getLog();
    }

    /**
     * Convert various object formats into a PHP array
     * @param mixed $value
     * @return array
     */
    private function prepareArguments(\Nethgui\View\ViewInterface $view, $value)
    {
        $a = array();
        foreach ($value as $k => $v) {
            if ($v instanceof \Nethgui\View\ViewableInterface) {
                $this->getLog()->deprecated();
            } elseif ($v instanceof \Traversable || is_array($v)) {
                $v = $this->prepareArguments($view, $v);
            }
            $a[$k] = $v;
        }
        return $a;
    }


    public function httpHeader($value)
    {
        $this->getLog()->deprecated();
        if($this->response instanceof \Nethgui\Controller\HttpResponse) {
            list($headerName, $headerValue) = explode(':', $value, 2);
            $this->response->setHttpHeaders(array($headerName => $headerValue));
        }
        return $this;
    }

    public function setDecoratorParameter($paramName, $paramValue)
    {
        $this->getLog()->deprecated();
        $this->view->getModule()->setDecoratorParameter($paramName, $paramValue);
        return $this;
    }

    public function setDecoratorTemplate($template)
    {
        $this->getLog()->deprecated();
        $this->view->getModule()->setDecoratorTemplate($template);
        return $this;
    }


    public function sendQuery($location)
    {
        if ($this->view->getTargetFormat() !== \Nethgui\View\View::TARGET_XHTML) {
            return $this->__call('sendQuery', array($location));
        }
        $this->httpRedirection(302, $location);

        return $this;
    }

    public function showMessage($text, $type)
    {
        $this->getLog()->deprecated();
        if ($this->view->getTargetFormat() !== \Nethgui\View\View::TARGET_XHTML) {
            return $this->__call('showMessage', array($text, $type));
        }

        $notificationModuleInstance = \Nethgui\array_head(array_filter($this->view->getModule()->getChildren(), function (\Nethgui\Module\ModuleInterface $child) {
           return $child->getIdentifier() === 'Notification';
        }));

        if($notificationModuleInstance instanceof \Nethgui\Module\Notification) {
            $notificationModuleInstance->showMessage($text, $type);
        }
    }

    public function showNotification(\Nethgui\Module\Notification\AbstractNotification $notification)
    {
        $this->getLog()->deprecated();
        if ($this->view->getTargetFormat() !== \Nethgui\View\View::TARGET_XHTML) {
            $v = $this->view->spawnView($this->view->getModule(), FALSE);
            $notification->prepareView($v);
            return $this->__call('showNotification', iterator_to_array($v));
        }

        $notificationModuleInstance = \Nethgui\array_head(array_filter($this->view->getModule()->getChildren(), function (\Nethgui\Module\ModuleInterface $child) {
           return $child->getIdentifier() === 'Notification';
        }));

        if($notificationModuleInstance instanceof \Nethgui\Module\Notification) {
            $notificationModuleInstance->showNotification($notification);
        }
    }


    /**
     * @param integer $code
     * @param string $location
     */
    private function httpRedirection($code, $location)
    {
        // Prefix the site URL to $location:
        if ( ! in_array(parse_url($location, PHP_URL_SCHEME), array('http', 'https'))) {
            $location = $this->view->getSiteUrl() . $location;
        }

        if($this->response instanceof \Nethgui\Controller\HttpResponse) {
            $this->response->setHttpStatus($code)->setHttpHeaders(array('Location' => $location));
        }
        return $this;
    }

}