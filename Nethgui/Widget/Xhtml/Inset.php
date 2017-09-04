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
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @internal
 */
class Inset extends \Nethgui\Widget\XhtmlWidget
{

    protected function getJsWidgetTypes()
    {
        return array();
    }

    protected function renderContent()
    {
        $name = $this->getAttribute('name');
        $flags = $this->getAttribute('flags', 0);

        if ( ! $this->hasAttribute('receiver')) {
            $this->setAttribute('receiver', $name);
        }

        $value = $this->view[$name];

        if ($value instanceof \Nethgui\View\ViewInterface) {
            // Render the view:
            $insetRenderer = $this->getRenderer()
                ->spawnRenderer($value)
                ->setDefaultFlags($flags | $this->getRenderer()->getDefaultFlags())
            ;
            $value = $insetRenderer->render();            
        } else {
            $insetRenderer = $this->getRenderer();
        }

        return (String) $this->wrapContent($value, $insetRenderer);
    }

    private function wrapContent($content, \Nethgui\Renderer\Xhtml $insetRenderer)
    {
        $flags = $this->getAttribute('flags');
        $flags = $flags & (~\Nethgui\Renderer\WidgetFactoryInterface::STATE_UNOBTRUSIVE);

        $panel = $this->getRenderer()
            ->panel($flags)
            ->setAttribute('tag', FALSE)
            ->setAttribute('receiver', $this->getAttribute('receiver'))
        ;

        $wrapFlags = $insetRenderer->calculateIncludeFlags($flags);

        $contentWidget = $this->getRenderer()->literal($content, $flags);

        if ($wrapFlags & \Nethgui\Renderer\WidgetFactoryInterface::INSET_FORM) {
            $flagEncMultipart = $wrapFlags & \Nethgui\Renderer\WidgetFactoryInterface::FORM_ENC_MULTIPART;
            $contentWidget = $insetRenderer->form($flags | $flagEncMultipart)->setAttribute('tag', FALSE)->insert($contentWidget);
        }

        $panel->insert($contentWidget);

        if ($wrapFlags & \Nethgui\Renderer\WidgetFactoryInterface::INSET_WRAP) {
            $panel
                ->setAttribute('tag', 'div');

            $cssClass = $this->getAttribute('class', 'Inset');

            if ($wrapFlags & \Nethgui\Renderer\WidgetFactoryInterface::INSET_DIALOG) {
                $cssClass .= ' Dialog';
            }

            $panel->setAttribute('class', $cssClass);
        }
        
        return $panel;
    }

}
