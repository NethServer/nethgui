<?php
namespace Nethgui\View;

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
 * A command composed of a sequence of other commands.
 * 
 * Each command is executed with the same receiver given to the command sequence.
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @internal
 */
class ViewCommandSequence implements \Nethgui\View\ViewCommandInterface
{

    private $commands = array();

    /**
     *
     * @var \Nethgui\View\ViewInterface
     */
    private $origin;
    private $selector;
    private $executed;

    /**
     *
     * @var \Nethgui\View\CommandReceiverInterface
     */
    private $receiver;

    public function __construct(\Nethgui\View\ViewInterface $origin, $selector)
    {
        $this->executed = FALSE;
        $this->origin = $origin;
        $this->selector = $selector;
    }

    public function __call($name, $arguments)
    {
        $command = new \Nethgui\View\Command($this->origin, $this->selector, $name, $arguments);
        $this->addCommand($command);
        return $this;
    }

    public function addCommand(\Nethgui\View\CommandInterface $command)
    {
        $this->commands[] = $command;
        return $this;
    }

    public function execute()
    {
        $this->executed = TRUE;
        foreach ($this->commands as $command) {
            $command->setReceiver($this->receiver)->execute();
        }
        return $this;
    }

    public function isExecuted()
    {
        return $this->executed === TRUE;
    }

    public function setReceiver(\Nethgui\View\CommandReceiverInterface $receiver)
    {
        $this->receiver = $receiver;
        return $this;
    }

    public function getOrigin()
    {
        return $this->origin;
    }

    public function getSelector()
    {
        return $this->selector;
    }

}