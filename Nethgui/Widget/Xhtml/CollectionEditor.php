<?php
namespace Nethgui\Widget\Xhtml;

/*
 * Copyright (C) 2012 Nethesis S.r.l.
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
 * A collection editor allows to create / update / delete the elements of a
 * collection of objects.
 * 
 * Each element belongs to a class, having its create / update / show template.
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class CollectionEditor extends \Nethgui\Widget\XhtmlWidget
{

    protected function getJsWidgetTypes()
    {
        return array('Nethgui:inputcontrol', 'Nethgui:buttonset', 'Nethgui:collectioneditor');
    }

    protected function renderContent()
    {
        $name = $this->getAttribute('name');
        $dimensions = explode('x', $this->getAttribute('dimensions', '20x30'));
        $rows = intval($dimensions[0]);
        $cols = intval($dimensions[1]);             
        $cssClass = trim('CollectionEditor ' . $this->getAttribute('class', ''));
        
        $content = '';
        
        $content .= $this->openTag('div', array(
            'class' => $cssClass . ' ' . $this->getClientEventTarget(), 
            'id' => $this->view->getUniqueId($name . '/wrapper')
            ));
               
        $content .= $this->openTag('textarea', array(
            'rows' => $rows,
            'cols' => $cols,
            'name' => $this->getControlName($name),
        ));        
        $content .= htmlspecialchars($this->view[$name]);
        $content .= $this->closeTag('textarea');


        $content .= $this->selfClosingTag('div', array(
           'class'  => 'elements',
            'id' => $this->view->getUniqueId($name)
        ));
        
        $content .= $this->closeTag('div');
        
        return $content;
    }
    

}