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
class HttpCommandReceiver extends \Nethgui\View\AbstractReceiverChain
{

    private $headers;

    public function __construct(\Nethgui\View\CommandReceiverInterface $nextReceiver = NULL)
    {
        parent::__construct($nextReceiver);
        $this->headers = array();
    }

    public function hasRefresh()
    {
        return $this->hasHeader('Refresh');
    }

    public function hasLocation()
    {
        return $this->hasHeader('Location');
    }

    private function hasHeader($headerName)
    {
        $len = strlen($headerName);
        foreach ($this->headers as $header) {
            if (strtoupper(substr($header, 0, $len)) === $headerName) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function getHttpHeaders()
    {
        return $this->headers;
    }

    public function executeCommand(\Nethgui\View\ViewInterface $origin, $selector, $name, $arguments)
    {
        if (method_exists($this, $name)) {
            $argumentsCopy = $arguments;
            array_unshift($argumentsCopy, $origin, $selector);
            call_user_func_array(array($this, $name), $argumentsCopy);
        }
        parent::executeCommand($origin, $selector, $name, $arguments);
    }

    protected function httpHeader(\Nethgui\View\ViewInterface $origin, $selector, $header)
    {
        $this->headers[] = $header;
    }

    protected function show(\Nethgui\View\ViewInterface $origin, $selector)
    {
        if ($origin->getTargetFormat() !== $origin::TARGET_XHTML) {
            return;
        }
        $this->httpRedirection($origin, 302, $origin->getModuleUrl($selector));
    }

    protected function sendQuery(\Nethgui\View\ViewInterface $origin, $selector, $location)
    {
        if ($origin->getTargetFormat() !== $origin::TARGET_XHTML) {
            return;
        }
        $this->httpRedirection($origin, 302, $location);
    }

    protected function reloadData(\Nethgui\View\ViewInterface $origin, $selector, $msec)
    {
        if ($origin->getTargetFormat() !== $origin::TARGET_XHTML) {
            return;
        }
        $seconds = intval($msec / 1000);
        if ($seconds < 4) {
            $seconds = 4;
        } elseif ($seconds > 10) {
            $seconds = 10;
        }

        if ( ! $this->hasLocation() && ! $this->hasRefresh()) {
            $this->httpHeader($origin, $selector, sprintf('Refresh: %d; url=%s', $seconds, $origin->getModuleUrl($selector)));
        }
    }

    /**
     *
     * @param integer $code
     * @param string $location
     */
    private function httpRedirection(\Nethgui\View\ViewInterface $origin, $code, $location)
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

        if ($this->hasLocation()) {
            $this->getLog()->debug(sprintf('%s: the Location header has been set already. Location `%s` is ignored.', __CLASS__, $location));
            return;
        }

        $this->headers[] = sprintf('HTTP/1.1 %d %s', $code, $codeMessage);
        $this->headers[] = 'Location: ' . $location;
    }

}

