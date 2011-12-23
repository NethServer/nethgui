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
class ElementList extends \Nethgui\Widget\XhtmlWidget
{

    private $childWrapTag;

    protected function getJsWidgetTypes()
    {
        $typeName = strtolower($this->getAttribute('class', 'ElementList'));

        if (in_array($typeName, array('buttonset', 'buttonlist'))) {
            return array('Nethgui:' . $typeName);
        }

        return array();
    }

    protected function renderContent()
    {
        $name = $this->getAttribute('name');
        $value = $this->getAttribute('value');
        $flags = $this->getAttribute('flags');
        $cssClass = $this->getAttribute('class', 'ElementList');
        $wrap = explode('/', $this->getAttribute('wrap', 'ul/li')) + array('div', 'div');

        $this->childWrapTag = $wrap[1];

        //if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::STATE_DISABLED) {
        //    $cssClass .= ' disabled';
        //}

        if ($this->hasAttribute('maxElements')) {
            $maxElements = intval($this->getAttribute('maxElements'));
            if ($maxElements > 0) {
                $cssClass .= ' v' . $maxElements;
            }
        }

        $content = $this->renderChildren();

        if ($content && $wrap[0]) {
            $content = $this->openTag($wrap[0], array('class' => $cssClass))
                . $content
                . $this->closeTag($wrap[0]);
        }

        return $content;
    }

    protected function wrapChild($childOutput)
    {
        if ( ! $this->childWrapTag) {
            return parent::wrapChild($childOutput);
        }

        $childTag = explode('.', $this->childWrapTag) + array(FALSE, FALSE);

        $content = '';
        if (strlen($childTag[0]) > 0) {
            $content .= $this->openTag($childTag[0]);
            $content .= parent::wrapChild($childOutput);
            $content .= $this->closeTag($childTag[0]);
        } else {
            $content .= parent::wrapChild($childOutput);
        }
        if (strlen($childTag[1]) > 0) {
            $content .= htmlspecialchars($childTag[1]);
        }

        return $content;
    }

}

