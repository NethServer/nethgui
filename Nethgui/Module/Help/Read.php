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
class Read extends Common
{

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        $view->setTemplate(FALSE);

        $module = $this->getTargetModule();
        if (is_null($module)) {
            return;
        }

        $filePath = $this->getHelpDocumentPath($this->getTargetModule());
        $view->getCommandList('/Main')->setDecoratorTemplate(function(\Nethgui\View\ViewInterface $renderer) {
                return $renderer['Help']['Read']['contents'];
            });

        if (NETHGUI_ENABLE_HTTP_CACHE_HEADERS) {
            $view->getCommandList()
                ->httpHeader(sprintf('Last-Modified: %s', date(DATE_RFC1123, $this->getPhpWrapper()->filemtime($filePath))))
                ->httpHeader(sprintf('Expires: %s', date(DATE_RFC1123, time() + 3600)))
            ;
        }

        $view['contents'] = $this->expandIncludes(
            $this->getPhpWrapper()->file_get_contents($filePath)
        );

        $contentLength = strlen($view['contents']);

        if ($contentLength > 0) {
            $view->getCommandList()->httpHeader(sprintf('Content-Length: %d', $contentLength));
        }
    }

}
