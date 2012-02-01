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
 * Records a sequence of method calls through the PHP magic method __call.
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
interface ViewCommandInterface
{
    /**
     * A magic method that creates and appends a Command object to a command
     * sequence.
     *
     * @api
     * @param string $name
     * @param array $arguments
     * @return \Nethgui\View\ViewCommandInterface The command sequence, with the registered method call appended
     */
    public function __call($name, $arguments);

}