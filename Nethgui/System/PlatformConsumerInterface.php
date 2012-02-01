<?php
namespace Nethgui\System;

/*
 * Copyright (C) 2012 Nethesis S.r.l.
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
 * An object that uses another PlatformInterface object
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
interface PlatformConsumerInterface
{

    /**
     * Set the Platform object
     *
     * @api
     * @return \Nethgui\System\PlatformConsumerInterface
     */
    public function setPlatform(\Nethgui\System\PlatformInterface $platform);

    /**
     * Get the Platform object
     *
     * @api
     * @return \Nethgui\System\PlatformInterface
     */
    public function getPlatform();

    /**
     * Tell if the Platform object has been set
     *
     * @api
     * @return boolean TRUE if the Platform has been set, FALSE otherwise
     */
    public function hasPlatform();
}