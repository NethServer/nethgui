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
 * Produces new command object instances
 *
 * @api
 * @since 1.0
 * @see \Nethgui\Core\ViewInterface
 */
interface CommandFactoryInterface
{
    const NOTIFY_SUCCESS = 0x0;
    const NOTIFY_WARNING = 0x1;
    const NOTIFY_ERROR = 0x2;
    const MASK_SEVERITY = 0x3;
    const NOTIFY_MODAL = 0x4;
    

    /**
     * Create command objects through the returned factory class instance
     *
     * @return \Nethgui\Core\CommandInterface
     */
    public function createUiCommand($methodName, $arguments);

    /**
     * Create a command that shows a dialog box.
     *
     * @param string|array $message Can be a plain string or an array of two elements <template:string, placeholders:array>
     * @param array $actions
     * @param integer $type
     * @return \Nethgui\Core\CommandInterface
     */
    public function showDialogBox($message, $actions = array(), $type = \Nethgui\Core\CommandFactoryInterface::NOTIFY_SUCCESS);
}