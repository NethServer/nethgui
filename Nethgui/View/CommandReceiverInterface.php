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
 * Implement command(s) semantics
 *
 * @see http://en.wikipedia.org/wiki/Command_pattern
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @deprecated since version 1.6
 */
interface CommandReceiverInterface
{
    /**
     * Specify an implementation for the given method and arguments.
     * 
     * The original view that generated the command and the selector string
     * that identifies the logical target provide more contextual informations.
     *
     * @param \Nethgui\View\ViewInterface $origin
     * @param string $selector
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function executeCommand(\Nethgui\View\ViewInterface $origin, $selector, $name, $arguments);
}

