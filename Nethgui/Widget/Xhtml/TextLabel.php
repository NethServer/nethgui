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
 * Attributes:
 *
 * - name
 * - flags
 * - escapeHtml
 * - tag
 * - template
 * - args
 * - icon-before
 * - icon-after
 * - class
 *
 */
class TextLabel extends \Nethgui\Widget\XhtmlWidget
{

    protected function renderContent()
    {
        $name = $this->getAttribute('name');
        $flags = $this->getAttribute('flags');
        $hsc = $this->getAttribute('escapeHtml', TRUE);
        $tag = $this->getAttribute('tag', 'span');
        $template = $this->getAttribute('template', '${0}');
        $cssClass = 'TextLabel';

        if ($this->hasAttribute('class')) {
            $cssClass .= ' ' . $this->getAttribute('class');
        }

        if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::STATE_DISABLED) {
            $cssClass .= ' disabled';
        }

        $unobstrusive = $flags & \Nethgui\Renderer\WidgetFactoryInterface::STATE_UNOBSTRUSIVE;

        $args = array('${0}' => $this->view->offsetExists($name) ? $this->view[$name] : '');

        if ($this->hasAttribute('args')) {
            $args = array();
            if ( ! is_array($this->getAttribute('args'))) {
                throw new \InvalidArgumentException(sprintf('%s: `args` attribute must be an array!', get_class($this)), 1322149926);
            }
            foreach ($this->getAttribute('args') as $argName => $argValue) {
                $args['${' . $argName . '}'] = strval($argValue);
            }
        }

        if ($unobstrusive) {
            $text = '';
        } else {
            $text = strtr($template, $args);
        }

        if ($hsc) {
            $text = htmlspecialchars($text);
        }

        if ($this->hasAttribute('name')) {
            $cssClass .= ' ' . $this->getClientEventTarget();
        }

        if ($this->hasAttribute('receiver')) {
            $attributes['id'] = $this->view->getUniqueId($this->getAttribute('receiver'));
        }

        $attributes = array('class' => $cssClass, 'data-options' => json_encode(array('template' => $template, 'hsc' => $hsc)));

        $content = $this->openTag($tag, $attributes);
        $content .= $text;
        $content .= $this->closeTag($tag);

        return $content;
    }

}
