<?php
namespace Nethgui\Renderer;

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
 * Prepare Command invocations for the client-side framework.
 *
 * Each command invocation is registered. Clients obtain marshalled commands
 * calling getMarshalledCommands()
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class MarshallingReceiver extends \Nethgui\Core\AbstractReceiverChain
{

    /**
     *
     * @var array
     */
    private $commands = array();

    public function executeCommand(\Nethgui\Core\ViewInterface $origin, $selector, $name, $arguments)
    {
        parent::executeCommand($origin, $selector, $name, $arguments);
        
        if ($name === 'showView') {
            $receiver = '#' . $origin->getUniqueId($arguments[0]);
            $arguments = array();
        } else {
            $receiver = '.' . $origin->getClientEventTarget($selector);
        }

        $argsArray = $this->prepareArguments($origin, $arguments);
        $this->addCommand($receiver, $name, $argsArray);
    }

    private function addCommand($receiver, $name, $arguments)
    {
        $this->commands[] = array(
            'R' => $receiver,
            'M' => $name,
            'A' => $arguments,
        );
    }

    /**
     *
     * @return array
     */
    public function getMarshalledCommands()
    {
        return $this->commands;
    }

    /**
     * Convert various object formats into a PHP array
     * @param mixed $value
     * @return array
     */
    private function prepareArguments(\Nethgui\Core\ViewInterface $view, $value)
    {
        $a = array();
        foreach ($value as $k => $v) {
            if ($v instanceof \Nethgui\Core\ViewableInterface) {
                $innerView = $view->spawnView($view->getModule());
                $v->prepareView($innerView, $view->getTargetFormat());
                $v = $this->prepareArguments($view, $innerView);
            } elseif ($v instanceof \Traversable || is_array($v)) {
                $v = $this->prepareArguments($view, $v);
            }
            $a[$k] = $v;
        }
        return $a;
    }

}
