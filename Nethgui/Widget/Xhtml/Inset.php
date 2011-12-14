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
        $value = $this->view->offsetGet($name);
        $content = '';

        if ($value instanceof \Nethgui\Core\ViewInterface) {
            $innerRenderer = $this->getRenderer()->spawnRenderer($value)->setDefaultFlags($flags);
            $content = (String) $this->wrapView($innerRenderer);
        } else {
            $content = (String) $this->view->literal($value, $flags);
        }

        return $content;
    }

    private function wrapView(\Nethgui\Renderer\Xhtml $inset)
    {
        $module = $inset->getModule();

        $content = $inset->render();
        $contentWidget = $this->view->literal($content);

        // 1. If we have a NOFORMWRAP give up here.
        if ($module instanceof \Nethgui\Core\Module\DefaultUiStateInterface
            && $module->getDefaultUiStyleFlags() & \Nethgui\Core\Module\DefaultUiStateInterface::STYLE_NOFORMWRAP) {
            return $contentWidget;
        }

        // 2. Wrap automatically a FORM tag only if instancof RequestHandler and no FORM tag has been emitted.
        if ($module instanceof \Nethgui\Core\RequestHandlerInterface
            && stripos($content, '<form ') === FALSE) {
            // Wrap a simple module into a FORM tag automatically
            $contentWidget = $inset->form()->insert($contentWidget);

            // Re-wrap a simple root-module into an Action div
            if ($module->getParent()->getIdentifier() === FALSE) {
                $contentWidget = $inset->panel()->setAttribute('class', 'Action')->insert($contentWidget);
            }
        }

        return $contentWidget;
    }

}
