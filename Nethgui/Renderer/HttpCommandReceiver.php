<?php
namespace Nethgui\Renderer;

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
 * HttpCommandReceiver
 *
 * Implements the command logic as HTTP redirects
 *
 */
class HttpCommandReceiver implements \Nethgui\Core\CommandReceiverInterface, \Nethgui\Core\GlobalFunctionConsumer
{

    /**
     *
     * @var \Nethgui\Core\ViewInterface
     */
    private $view;

    /**
     *
     * @var \Nethgui\Core\CommandReceiverInterface
     */
    private $fallbackReceiver;

    /**
     *
     * @var \Nethgui\Core\GlobalFunctionWrapper
     */
    private $globalFunctionWrapper;

    public function __construct(\Nethgui\Core\ViewInterface $view, \Nethgui\Core\CommandReceiverInterface $fallbackReceiver = NULL)
    {
        $this->view = $view;
        $this->fallbackReceiver = $fallbackReceiver;
        $this->globalFunctionWrapper = new \Nethgui\Core\GlobalFunctionWrapper();
    }

    public function executeCommand($name, $arguments)
    {
        if ( ! method_exists($this, $name) && isset($this->fallbackReceiver)) {
            return $this->fallbackReceiver->executeCommand($name, $arguments);
        }
        return call_user_func_array(array($this, $name), $arguments);
    }

    public function cancel()
    {
        $this->httpRedirection(302, $this->view->getModuleUrl('..'));
    }

    public function activateAction($actionId, $path = NULL, $prevComponent = NULL)
    {
        if (is_null($path)) {
            $path = $actionId;
        }

        $this->httpRedirection(302, $this->view->getModuleUrl($path));
    }

    public function enable()
    {
        $this->httpRedirection(302, $this->view->getModuleUrl());
    }

    public function redirect($url)
    {
        $this->httpRedirection(302, $url);
    }

    /**
     *
     * @param integer $code
     * @param string $location
     */
    private function httpRedirection($code, $location)
    {
        $messages = array(
            '201' => 'Created',
            '301' => 'Moved Permanently',
            '302' => 'Found',
            '303' => 'See Other',
            '307' => 'Temporary Redirect'
        );

        if (isset($messages[strval($code)])) {
            $codeMessage = $messages[strval($code)];
        } else {
            throw new \DomainException(sprintf('Unknown status code for redirection: %d',  intval($code)), 1322149333);
        }

        // Prefix the site URL to $location:
        if ( ! in_array(parse_url($location, PHP_URL_SCHEME), array('http', 'https'))) {
            $location = $this->view->getSiteUrl() . $location;
        }

        $this->globalFunctionWrapper->header(sprintf('HTTP/1.1 %d %s', $code, $codeMessage));
        $this->globalFunctionWrapper->header('Location: ' . $location);

        $ob_status = $this->globalFunctionWrapper->ob_get_status();

        if ( ! empty($ob_status)) {
            $this->globalFunctionWrapper->ob_end_clean();
        }

        $this->globalFunctionWrapper->phpExit(0);
    }

    public function setGlobalFunctionWrapper(\Nethgui\Core\GlobalFunctionWrapper $object)
    {
        $this->globalFunctionWrapper = $object;
    }

}

