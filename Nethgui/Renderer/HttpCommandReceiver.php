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
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class HttpCommandReceiver extends \Nethgui\Core\AbstractReceiverChain
{

    private $headers;

    public function __construct(\Nethgui\Core\CommandReceiverInterface $nextReceiver = NULL)
    {
        parent::__construct($nextReceiver);
        $this->headers = array();
    }

    public function hasRedirect()
    {
        foreach ($this->headers as $header) {
            if (strtoupper(substr($header, 0, 8)) === 'LOCATION') {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function getHttpHeaders()
    {
        return $this->headers;
    }

    public function executeCommand(\Nethgui\Core\ViewInterface $origin, $selector, $name, $arguments)
    {
        if (method_exists($this, $name)) {
            $argumentsCopy = $arguments;
            array_unshift($argumentsCopy, $origin, $selector);
            call_user_func_array(array($this, $name), $argumentsCopy);
        }
        parent::executeCommand($origin, $selector, $name, $arguments);
    }

    protected function httpHeader(\Nethgui\Core\ViewInterface $origin, $selector, $header)
    {
        $this->headers[] = $header;
    }

    protected function show(\Nethgui\Core\ViewInterface $origin, $selector)
    {
        if ($origin->getTargetFormat() !== $origin::TARGET_XHTML) {
            return;
        }
        $this->httpRedirection($origin, $selector, 302, $origin->getModuleUrl($selector));
    }

    protected function sendQuery(\Nethgui\Core\ViewInterface $origin, $selector, $path)
    {
        if ($origin->getTargetFormat() !== $origin::TARGET_XHTML) {
            return;
        }
        $this->httpRedirection($origin, $selector, 302, $path);
    }

//    protected function showView(\Nethgui\Core\ViewInterface $origin, $selector, $location)
//    {
//        $this->httpRedirection($origin, $selector, $code, $location);
//    }
//    protected function cancel(\Nethgui\Core\ViewInterface $origin, $selector)
//    {
//        $this->httpRedirection($origin, $selector, 302, $origin->getModuleUrl('..'));
//    }
//
//    protected function activateAction(\Nethgui\Core\ViewInterface $origin, $selector, $actionId, $path = NULL, $prevComponent = NULL)
//    {
//        if (is_null($path)) {
//            $path = $actionId;
//        }
//
//        $this->httpRedirection($origin, $selector, 302, $origin->getModuleUrl($path));
//    }
//
//    protected function enable(\Nethgui\Core\ViewInterface $origin, $selector)
//    {
//        $this->httpRedirection($origin, $selector, 302, $origin->getModuleUrl());
//    }
//
//    protected function redirect(\Nethgui\Core\ViewInterface $origin, $selector, $url)
//    {
//        $this->httpRedirection($origin, $selector, 302, $url);
//    }

    /**
     *
     * @param integer $code
     * @param string $location
     */
    private function httpRedirection(\Nethgui\Core\ViewInterface $origin, $selector, $code, $location)
    {
        $messages = array(
            '201' => 'Created',
            '205' => 'Reset Content',
            '301' => 'Moved Permanently',
            '302' => 'Found',
            '303' => 'See Other',
            '307' => 'Temporary Redirect'
        );

        if (isset($messages[strval($code)])) {
            $codeMessage = $messages[strval($code)];
        } else {
            throw new \DomainException(sprintf('Unknown status code for redirection: %d', intval($code)), 1322149333);
        }

        // Prefix the site URL to $location:
        if ( ! in_array(parse_url($location, PHP_URL_SCHEME), array('http', 'https'))) {
            $location = $origin->getSiteUrl() . $location;
        }

        if ( ! $this->hasRedirect()) {
            $this->headers[] = sprintf('HTTP/1.1 %d %s', $code, $codeMessage);
            $this->headers[] = 'Location: ' . $location;
        }
    }

}

