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
class LegacyCommandBag extends \ArrayObject implements \Nethgui\Component\DependencyConsumer
{
    /**
     *
     * @var \Nethgui\View\View
     */
    private $view;
    private $currentSelector, $currentOrigin;

    /**
     * @var \Nethgui\Model\UserNotifications
     */
    private $userNotifications;

    /**
     *
     * @var \Nethgui\Renderer\HttpResponse
     */
    public $response;

    public function __construct(\Nethgui\View\View $view)
    {
        parent::__construct(array());
        $this->view = $view;
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
        $this->response->addHeader($value);
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
        if ($this->view->getTargetFormat() === \Nethgui\View\View::TARGET_JSON) {
            return $this->__call('sendQuery', array($location));
        }
        $this->httpRedirection(302, $location);

        return $this;
    }

    public function showMessage($text, $type)
    {
        $this->getLog()->deprecated();
        if ($type === \Nethgui\Module\Notification\AbstractNotification::NOTIFY_ERROR) {
            $this->userNotifications->error($text);
        } else {
            $this->userNotifications->info($text);
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
            $url = $this->view->getSiteUrl() . $location;
        }

        $this->response
            ->setStatus(302)
            ->addHeader('Location: ' . $url)
        ;
        return $this;
    }

    public function getDependencySetters()
    {
        $response = &$this->response;
        $notifications = &$this->userNotifications;

        return array(
            'UserNotifications' => function ($n) use (&$notifications) {
            $notifications = $n;
        },
            'HttpResponse' => function ($r) use (&$response) {
            $response = $r;
        }
        );
    }

}