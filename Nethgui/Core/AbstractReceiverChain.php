<?php
namespace Nethgui\Core;

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
 * Forwards the execution opeation to another receiver
 *
 * @see http://en.wikipedia.org/wiki/Chain-of-responsibility_pattern
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
abstract class AbstractReceiverChain implements CommandReceiverInterface
{

    /**
     * @var \Nethgui\Core\CommandReceiverInterface
     */
    private $nextReceiver;

    /**
     *
     * @param \Nethgui\Core\CommandReceiverInterface $nextReceiver (optional)
     */
    public function __construct(\Nethgui\Core\CommandReceiverInterface $nextReceiver = NULL)
    {
        $this->nextReceiver = is_null($nextReceiver) ? \Nethgui\Core\NullReceiver::getNullInstance() : $nextReceiver;
    }

    /**
     * Get the next receiver of the chain
     *
     * @return \Nethgui\Core\CommandReceiverInterface
     */
    public function getNextReceiver()
    {
        return $this->nextReceiver;
    }

    /**
     * Set the next receiver of the chain
     * 
     * @param \Nethgui\Core\CommandReceiverInterface $receiver
     */
    public function setNextReceiver(\Nethgui\Core\CommandReceiverInterface $receiver)
    {
        $this->nextReceiver = $receiver;
    }

    public function executeCommand(\Nethgui\Core\ViewInterface $origin, $selector, $name, $arguments)
    {
        return $this->getNextReceiver()->executeCommand($origin, $selector, $name, $arguments);
    }

}