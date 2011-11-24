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
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class Show extends Common
{

    public function prepareView(\Nethgui\Core\ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);

        if (is_null($this->module)) {
            return;
        }

        $filePath = $this->getHelpDocumentPath($this->module);

        $content = $this->readHelpDocumentContent($filePath);

        if ($content === FALSE) {
            $view['content'] = 'Error loading help contents for module ' . $this->module->getIdentifier();
            return;
        }

        $view['content'] = $content;
    }


    private function readHelpDocumentContent($filePath)
    {
        $document = new \XMLReader();
        if ( $document->open('file://' . $filePath, 'utf-8', LIBXML_NOENT) !== TRUE) {
            return FALSE;
        }

        // Advance to BODY tag:
        while ($document->name != 'body' && $document->read());
        while ($document->name != 'div' && $document->read());

        $content = $document->readInnerXml();
        $document->close();

        return $content;
    }
}
