<?php
namespace Nethgui\Core\Module;

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
 * Describe the behaviour to be applied in the user interface
 *
 * @see Controller
 */
interface DefaultUiStateInterface
{
    const STYLE_DIALOG = 0x1;
    const STYLE_ENABLED = 0x2;

    const STYLE_CONTAINER_TABLE = 0x4;
    const STYLE_CONTAINER_TABS = 0x08;
    const STYLE_NOFORMWRAP = 0x10;

    /**
     * @return int The style flags
     */
    public function getDefaultUiStyleFlags();
}
