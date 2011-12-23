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

    protected function renderContent()
    {
        $name = $this->getAttribute('name');
        $flags = $this->getAttribute('flags', 0);
        $value = $this->view[$name];

        $content = '';
        if ($value instanceof \Nethgui\Core\ViewInterface) {
            $renderer = $this->getRenderer()
                ->spawnRenderer($value)
                ->setDefaultFlags($flags | $this->getRenderer()->getDefaultFlags())
            ;
            $content = (String) $this->wrapSubview($renderer);
        } else {
            $content = (String) $this->getRenderer()->literal($value, $flags);
        }

        $content = (String) $this->wrapContent($content);

        return $content;
    }

    private function wrapContent($content)
    {
        $wrapper = $this->getRenderer()->panel()->setAttribute('tag', FALSE);

        if ($this->hasAttribute('class')) {
            $wrapper
                ->setAttribute('tag', 'div')
                ->setAttribute('class', $this->getAttribute('class'))
            ;
        }

        if ($this->hasAttribute('receiver')) {
            $wrapper->setAttribute('receiver', $this->getAttribute('receiver'));
        }

        $wrapper->insert($this->getRenderer()->literal($content));

        return $wrapper;
    }

    private function wrapSubview(\Nethgui\Renderer\Xhtml $renderer)
    {
        $module = $renderer->getModule();

        $content = $renderer->render();
        $contentWidget = $this->view->literal($content, $this->getAttribute('flags', 0));

        // 1. If we have a NOFORMWRAP give up here.
        if ($module instanceof \Nethgui\Core\Module\DefaultUiStateInterface
            && $module->getDefaultUiStyleFlags() & \Nethgui\Core\Module\DefaultUiStateInterface::STYLE_NOFORMWRAP) {
            return $contentWidget;
        }

        // 2. Wrap automatically a FORM tag only if instance of RequestHandler and no FORM tag has been emitted.
        if ($module instanceof \Nethgui\Core\RequestHandlerInterface
            && stripos($content, '<form ') === FALSE) {
            // Wrap a simple module into a FORM tag automatically
            $contentWidget = $renderer->form()->insert($contentWidget);

            // Re-wrap a simple root-module into an Action div
            if ($module->getParent()->getIdentifier() === FALSE) {
                $contentWidget = $renderer->panel()->setAttribute('class', 'Action')->insert($contentWidget);
            }
        }
        
        return $contentWidget;
    }

}
