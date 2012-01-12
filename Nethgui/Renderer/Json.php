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
class Json extends AbstractRenderer
{

    /**
     *
     * @var \Nethgui\Renderer\MarshallingReceiver
     */
    private $receiver;

    public function __construct(\Nethgui\Client\View $view, \Nethgui\Command\CommandReceiverInterface $receiver)
    {
        parent::__construct($view);
        $this->receiver = $receiver;
    }

    private function deepWalk(&$events)
    {
        // iterative tree walk - $q is the node queue:
        $q = array($this->view);

        while ($view = array_shift($q)) {

            foreach ($view as $offset => $value) {

                $eventTarget = $view->getClientEventTarget($offset);

                if ($value instanceof \Nethgui\Core\ViewInterface) {
                    // honor the ViewInterface contract: if template is FALSE
                    // skip rendering, otherwise enqueue:
                    if ($value->getTemplate() !== FALSE) {
                        $q[] = $value;
                    }
                    continue;
                }

                if ($value instanceof \Traversable) {
                    $eventData = $this->traversableToArray($value);
                } else {
                    $eventData = $value;
                }

                $events[] = array($eventTarget, $eventData);
            }
        }

        foreach ($this->view->getCommands() as $command) {
            if ( ! $command instanceof \Nethgui\Command\CommandInterface || $command->isExecuted()) {
                continue;
            }

            // Execute all still-unexecuted commands sent to widgets and renderers:
            $command->setReceiver($this->receiver)->execute();
        }
    }

    /**
     * Convert a \Traversable object to a PHP array
     * @param \Traversable $value
     * @return array
     */
    function traversableToArray(\Traversable $value)
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

    public function render()
    {
        $events = array();
        $output = array();

        $this->deepWalk($events);

        if (count($events) > 0) {
            $output = $events;
        }

        $commands = array();
        
        $this->receiver->executeCommand($this->view, NULL, 'getMarshalledCommands', array(&$commands));
      
        if (count($commands) > 0) {
            $output[] = array('__COMMANDS__', $commands);
        }

        return json_encode($output);
    }

    public function getCharset()
    {
        return 'UTF-8';
    }

    public function getContentType()
    {
        return 'application/json';
    }

}
