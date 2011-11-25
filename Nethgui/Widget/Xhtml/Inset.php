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
class Inset extends \Nethgui\Widget\XhtmlWidget
{

    public function render()
    {
        $name = $this->getAttribute('name');
        $flags = $this->getAttribute('flags', 0);
        $value = $this->view->offsetGet($name);
        $content = '';

        if ($value instanceof \Nethgui\Core\ViewInterface && $this->view instanceof \Nethgui\Core\CommandReceiverAggregateInterface) {
            $innerRenderer = new \Nethgui\Renderer\Xhtml($value, $this->view->getTemplateResolver(), $flags, $this->view->getCommandReceiver());
            $content = (String) $this->wrapView($innerRenderer);
        } else {
            $content = (String) $this->view->literal($value, $flags);
        }

        return $content;
    }

    private function wrapView(\Nethgui\Renderer\Xhtml $inset)
    {
        $module = $inset->getModule();

        $content = (String) $inset;
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
            if ($module->getParent() === NULL) {
                $contentWidget = $inset->panel()->setAttribute('class', 'Action')->insert($contentWidget);
            }
        }

        return $contentWidget;
    }

}
