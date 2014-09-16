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
 *
 */
class ProgressBar extends \Nethgui\Widget\Xhtml\TextLabel
{
    protected $cssClass = 'TextLabel ProgressbarText';

    public function __construct(\Nethgui\View\ViewInterface $view)
    {
        parent::__construct($view);
        $this->setAttribute('template', '${0}%');
        $this->setAttribute('tag', 'span');
    }

    public function renderContent()
    {
        $cssClass = 'Progressbar';
        if ($this->hasAttribute('name')) {
            $cssClass .= ' ' . $this->getClientEventTarget();
        }
        $content = $this->openTag('div', array('class' => $cssClass));
        $content .= parent::renderContent();
        $content .= $this->closeTag('div');
        return $content;
    }
}
