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

    public function prepareView(\Nethgui\Core\ViewInterface $view)
    {
        $module = $this->getTargetModule();

        if (is_null($module)) {
            $view->setTemplate(FALSE);
        } else {
            $view->getCommandListFor('/Main')->setDecoratorTemplate(array($this, 'renderDocument'));
        }
    }

    public function renderDocument(\Nethgui\Renderer\TemplateRenderer $renderer)
    {
        $filePath = $this->getHelpDocumentPath($this->getTargetModule());

        return $this->globalFunctions->file_get_contents($filePath);
    }

}
