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

        $receiver = $origin->getClientEventTarget($selector);

//        if ($selector === '/Notification'
//            && $name === 'showNotification'
//            && $arguments[0] instanceof \Nethgui\Client\AbstractNotification) {
//            // Replace the abstract notification object with its identifier:
//            $arguments[0] = $arguments[0]->getIdentifier();
//        }

        $this->addCommand($receiver, $name, $arguments);
        
//        if ($name == 'activateAction') {
//            $receiver = '';
//            $tmp = array(
//                $origin->getUniqueId($arguments[0]),
//                $origin->getModuleUrl($arguments[0]),
//                $origin->getUniqueId()
//            );
//
//            if (isset($arguments[1])) {
//                $tmp[1] = $origin->getModuleUrl($arguments[1]);
//            }
//
//            if (isset($arguments[2])) {
//                $tmp[2] = $origin->getUniqueId($arguments[2]);
//            }
//
//            $arguments = $tmp;
//        } elseif ($name == 'redirect' || $name == 'queryUrl') {
//            $receiver = '';
//            $arguments[0] = $origin->getModuleUrl($arguments[0]);
//        } elseif ($name == 'delay'
//            && $arguments[0] instanceof \Nethgui\Core\CommandInterface) {
//            // replace the first argument with the array equivalent
//            $arguments[0] = $arguments[0]->setReceiver($this)->execute();
//        } else {
//
//        }
    }

    private function addCommand($receiver, $name, $arguments)
    {
        $this->commands[] = array(
            'receiver' => '.' . $receiver,
            'methodName' => $name,
            'arguments' => \Nethgui\traversable_to_array($arguments),
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

}
