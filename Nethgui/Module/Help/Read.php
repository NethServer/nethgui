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
class Read extends Common
{

    public function process()
    {
        if (is_null($this->module)) {
            return;
        }

        $filePath = $this->getHelpDocumentPath($this->module);

        if ( ! $this->globalFunctions->is_readable($filePath)) {
            throw new \Nethgui\Exception\HttpStatusClientError('File not found', 404);
        }

        $this->globalFunctions->header("Content-Type: text/html; charset=UTF-8");
        $this->globalFunctions->readfile($filePath);

        exit;
    }

}

