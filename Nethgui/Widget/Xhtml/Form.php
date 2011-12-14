<?php
namespace Nethgui\Widget\Xhtml;

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
 * Wrap FORM tag around a Panel object
 */
class Form extends Panel
{

    protected function renderContent()
    {                       
        $action = $this->getAttribute('action', '');
        $this->setAttribute('class', $this->getAttribute('class', FALSE));
        $this->setAttribute('name', FALSE);

        $content = '';
        $content .= $this->openTag('form', array('method' => 'post', 'action' => $this->view->getModuleUrl($action)));
        $content .= parent::renderContent();
        $content .= $this->closeTag('form');

        return $content;
    }

}
