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
 * Transform a view in a JSON string
 * 
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class Json extends AbstractRenderer implements \Nethgui\Core\DelegatingCommandReceiverInterface
{

    /**
     *
     * @var \Nethgui\Core\CommandReceiverInterface
     */
    private $receiverDelegate;

    public function __construct(\Nethgui\Core\ViewInterface $view, \Nethgui\Core\CommandReceiverInterface $receiverDelegate)
    {
        parent::__construct($view);
        $this->receiverDelegate = $receiverDelegate;
    }

    private function deepWalk(&$events, &$commands)
    {
        foreach ($this as $offset => $value) {

            $eventTarget = $this->getClientEventTarget($offset);
            if ($value instanceof \Nethgui\Core\ViewInterface) {
                if ($value->getTemplate() === FALSE) {
                    // honor the ViewInterface contract: if template is FALSE skip rendering
                    continue;
                }
                if ( ! $value instanceof Json) {
                    $value = new Json($value, $this);
                }
                $value->deepWalk($events, $commands);
                continue;
            } elseif ($value instanceof \Nethgui\Core\CommandInterface) {
                $receiver = new JsonReceiver($this->view, $offset, $this);
                $commands[] = $value->setReceiver($receiver)->execute();
                continue;
            } elseif ($value instanceof \Traversable) {
                $eventData = $this->traversableToArray($value);
            } else {
                $eventData = $value;
            }

            $events[] = array($eventTarget, $eventData);
        }
    }

    /**
     * Convert a \Traversable object to a PHP array
     * @param \Traversable $value
     * @return array
     */
    private function traversableToArray(\Traversable $value)
    {
        $a = array();
        foreach ($value as $k => $v) {
            if ($v instanceof \Traversable) {
                $v = $this->traversableToArray($v);
            }
            $a[$k] = $v;
        }
        return $a;
    }

    protected function render()
    {
        $events = array();
        $commands = array();

        $this->deepWalk($events, $commands);
        if (count($commands) > 0) {
            $events[] = array('ClientCommandHandler', $commands);
        }

        return json_encode($events);
    }

    public function executeCommand($name, $arguments)
    {
        return $this->getDelegatedCommandReceiver()->executeCommand($name, $arguments);
    }

    public function getDelegatedCommandReceiver()
    {
        return $this->receiverDelegate;
    }

}

/**
 * Prepare Command invocations for the client-side framework
 */
class JsonReceiver implements \Nethgui\Core\DelegatingCommandReceiverInterface
{

    private $offset;

    /**
     *
     * @var \Nethgui\Core\ViewInterface
     */
    private $view;

    /**
     * @var \Nethgui\Core\CommandReceiverInterface
     */
    private $delegatedReceiver;

    public function __construct(\Nethgui\Core\ViewInterface $view, $offset, \Nethgui\Core\CommandReceiverInterface $delegatedReceiver)
    {
        $this->view = $view;
        $this->offset = $offset;
        $this->delegatedReceiver = $delegatedReceiver;
    }

    public function getDelegatedCommandReceiver()
    {
        return $this->delegatedReceiver;
    }

    public function executeCommand($name, $arguments)
    {
        if ($name == 'delay'
            && $arguments[0] instanceof \Nethgui\Core\CommandInterface) {
            $receiver = '';
            // replace the first argument with the array equivalent
            $arguments[0] = $arguments[0]->setReceiver(clone $this)->execute();
        } elseif ($name == 'redirect' || $name == 'queryUrl') {
            $receiver = '';
            $arguments[0] = $this->view->getModuleUrl($arguments[0]);
        } elseif ($name == 'activateAction') {
            $receiver = '';

            $tmp = array(
                $this->view->getUniqueId($arguments[0]),
                $this->view->getModuleUrl($arguments[0]),
                $this->view->getUniqueId()
            );

            if (isset($arguments[1])) {
                $tmp[1] = $this->view->getModuleUrl($arguments[1]);
            }

            if (isset($arguments[2])) {
                $tmp[2] = $this->view->getUniqueId($arguments[2]);
            }

            $arguments = $tmp;
        } elseif ($name == 'showDialogBox') {
            $receiver = '#NotificationArea';
            $name = 'addNotification';

            $dialogBox = $arguments[0];
            $arguments = array();

            if ($dialogBox instanceof \Nethgui\Client\DialogBox) {
                $message = $dialogBox->getMessage();
                $arguments[0] = array(
                    'message' => $this->view->getTranslator()->translate($dialogBox->getModule(), $message[0], $message[1]),
                    'actions' => $dialogBox->getActions(),
                    'transient' => $dialogBox->isTransient(),
                    'type' => $dialogBox->getType(),
                    'dialogId' => $dialogBox->getId(),
                    'errors' => array(),
                );
            }

            $this->getDelegatedCommandReceiver()->executeCommand($name, $arguments);
        } elseif ($name == 'dismissDialogBox') {

            $this->getDelegatedCommandReceiver()->executeCommand($name, $arguments);
        } elseif ($name == 'debug' || $name == 'alert') {
            $receiver = '';
        } else {
            $receiver = is_numeric($this->offset) ? '#' . $this->view->getUniqueId() : '.' . $this->view->getClientEventTarget($this->offset);
        }

        return $this->commandForClient($receiver, $name, $arguments);
    }

    private function commandForClient($receiver, $name, $arguments)
    {
        return array(
            'receiver' => $receiver,
            'methodName' => $name,
            'arguments' => $arguments,
        );
    }

}
