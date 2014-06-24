<?php

namespace Nethgui\Renderer;

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
class HttpResponse implements \Nethgui\Controller\ResponseInterface
{
    /**
     *
     * @var \Exception
     */
    private $error;

    /**
     *
     * @var callable
     */
    private $handler;

    /**
     *
     * @var string
     */
    private $next;

    /**
     *
     * @var \Nethgui\View\ViewInterface
     */
    private $view;

    /**
     *
     * @var \Nethgui\Controller\RequestInterface
     */
    private $request;

    /**
     *
     * @var \Nethgui\Component\DependencyInjectorInterface
     */
    private $di;

    /**
     *
     * @var array
     */
    private $httpStatusMessages = array(
        '200' => 'Success',
        '201' => 'Created',
        '302' => 'Found',
        '400' => 'Bad request',
        '403' => 'Forbidden',
        '500' => 'Internal server error',
    );

    public function __construct(\Nethgui\Controller\RequestInterface $request, \Nethgui\View\ViewInterface $view, \Nethgui\Component\DependencyInjectorInterface $di)
    {
        $this->view = $view;
        $this->request = $request;
        $this->handler = array($this, 'defaultHandler');
        $this->di = $di;
    }

    /**
     *
     * @param string $targetFormat
     * @param \Nethgui\View\ViewInterface $view
     * @return \Nethgui\Renderer\AbstractRenderer
     */
    private function createRenderer()
    {
        $targetFormat = $this->request->getFormat();
        $view = $this->view;
        $moduleInjector = $this->di;
        $filenameResolver = $this->filenameResolver;

        if ($targetFormat === 'json') {
            $renderer = new \Nethgui\Renderer\Json($view);
        } elseif ($targetFormat === 'xhtml') {
            $renderer = new \Nethgui\Renderer\Xhtml($view, $filenameResolver, 0);
        } else if ($targetFormat === 'js') {
            $renderer = new \Nethgui\Renderer\TemplateRenderer($view, $filenameResolver, 'application/javascript', 'UTF-8');
        } elseif ($targetFormat === 'css') {
            $renderer = new \Nethgui\Renderer\TemplateRenderer($view, $filenameResolver, 'text/css', 'UTF-8');
        } else {
            $renderer = new \Nethgui\Renderer\TemplateRenderer($view, $filenameResolver, 'text/plain', 'UTF-8');
        }

        $moduleInjector->inject($renderer);

        return $renderer;
    }

    public function send()
    {
        $httpStatus = 200;
        $httpHeaders = array();

        if ($this->error instanceof \Nethgui\Exception\HttpException) {
            $httpStatus = $this->error->getHttpStatusCode();
            $this->httpStatusMessages[$httpStatus] = $this->error->getMessage();
            $renderer = $this->createRenderer();
            $content = $renderer->render();

            $httpHeaders = array(
                sprintf('Content-Type: %s', $renderer->getContentType()) . (
                $renderer->getCharset() ?
                    sprintf('; charset=%s', $renderer->getCharset()) : ''
                )
            );
        } elseif ($this->error instanceof \Exception) {
            $this->handler = array($this, 'exceptionHandler');
        } else {
            $renderer = $this->createRenderer();
            $content = $renderer->render();

            $httpHeaders = array(
                sprintf('Content-Type: %s', $renderer->getContentType()) . (
                $renderer->getCharset() ?
                    sprintf('; charset=%s', $renderer->getCharset()) : ''
                )
            );
        }

        call_user_func($this->handler, $content, $httpStatus, $httpHeaders);
    }

    public function exceptionHandler($content, $httpStatus, $httpHeaders)
    {
        $ex = $this->error;
        header(sprintf('HTTP/1.1 %s %s', 500, $ex->getMessage()));
        header('Content-Type: text/plain; charset=UTF-8');
        echo sprintf("Nethgui:\n\n    %d - %s [%s]\n\n\n\n", 500, $ex->getMessage(), $ex->getCode());

        if ($backtrace) {
            echo sprintf("Exception backtrace:\n\n%s\n\n", $ex->getTraceAsString());
            $prev = $ex->getPrevious();
            if ($prev instanceof \Exception) {
                echo sprintf("Previous %s:\n\n    %s [%s]\n\n", get_class($prev), $prev->getMessage(), $prev->getCode());
                echo $prev->getTraceAsString();
            }
        }
        flush();
    }

    public function defaultHandler($content, $httpStatus, $httpHeaders)
    {
        if (isset($this->httpStatusMessages[strval($httpStatus)])) {
            $statusMessage = $this->httpStatusMessages[strval($httpStatus)];
        } else {
            $statusMessage = 'Unknown ' . $httpStatus . ' response message';
        }

        header(sprintf('HTTP/1.1 %d %s', $httpStatus, $statusMessage));
        array_map('header', $httpHeaders);
        echo $content;
        flush();
    }

    public function setError(\Exception $ex)
    {
        $this->error = $ex;
        return $this;
    }

    public function setHandler($handler)
    {
        $this->handler = $handler;
        return $this;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function setNext($path)
    {
        $this->next = $path;
        return $this;
    }

}