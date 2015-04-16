<?php

namespace Nethgui\Module\Help;

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
 * Prints out the help HTML document
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class Read extends Common implements \Nethgui\Component\DependencyConsumer
{

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {


        $module = $this->getTargetModule();
        if (is_null($module)) {
            return;
        }

        $filePath = $this->getHelpDocumentPath($this->getTargetModule());

        if ( ! $this->getPhpWrapper()->file_exists($filePath)) {
            throw new \Nethgui\Exception\HttpException(sprintf("%s: resource not found", __CLASS__), 404, 1351702294);
        }

        $readModule = $this;
        $phpWrapper = $readModule->getPhpWrapper();

        // Override the root view template, to skip the default decorator template.
        $this->rootView->setTemplate(function(\Nethgui\View\ViewInterface $renderer, $T, \Nethgui\Utility\HttpResponse $response) use ($readModule, $phpWrapper, $filePath) {
            $contents = $readModule->expandIncludes(
                $phpWrapper->file_get_contents($filePath)
            );

            if (NETHGUI_ENABLE_HTTP_CACHE_HEADERS) {
                $response->addHeader(sprintf('Last-Modified: %s', date(DATE_RFC1123, $phpWrapper->filemtime($filePath))));
                $response->addHeader(sprintf('Expires: %s', date(DATE_RFC1123, time() + 3600)));
            }
            $response->addHeader(sprintf('Content-Length: %d', strlen($contents)));
            return $contents;
        });
    }

    public function setRootView(\Nethgui\View\ViewInterface $view)
    {
        $this->rootView = $view;
        return $this;
    }

    public function getDependencySetters()
    {
        return array_merge(parent::getDependencySetters(), array('View' => array($this, 'setRootView')));
    }

}