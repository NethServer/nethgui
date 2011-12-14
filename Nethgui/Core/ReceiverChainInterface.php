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
 * An object can delegate another object to receive commands
 *
 * @see http://en.wikipedia.org/wiki/Command_pattern
 * @see http://en.wikipedia.org/wiki/Chain_of_responsibility_pattern
 * @see \Nethgui\Core\CommandReceiverInterface
 * @author Davide Principi <davide.principi@nethesis.it>
 */
interface ReceiverChainInterface extends CommandReceiverInterface
{
    /**
     * Set the receiver where unhandled or partially-handled calls are
     * forwarded to.
     *
     * @see #executeCommand()
     *
     * @param \Nethgui\Core\CommandReceiverInterface $receiver The next receiver of the chain
     * @return \Nethgui\Core\ReceiverChainInterface The object itself
     */
    public function setNextReceiver(\Nethgui\Core\CommandReceiverInterface $receiver);
}
