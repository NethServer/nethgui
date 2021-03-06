<?php
namespace Nethgui\Module;

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
 * A complex module, composed by other modules, must implement this interface.
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
interface ModuleCompositeInterface
{

    /**
     * @api
     * @return array An array of ModuleInterface implementing objects.
     */
    public function getChildren();

    /**
     * Adds a child to this Composite. Implementations must send a setParent()
     * message to $module.
     *
     * @todo Add return $this on implementations
     *
     * @api
     * @param ModuleInterface $module The child module.
     * @return ModuleCompositeInterface The composite module
     */
    public function addChild(\Nethgui\Module\ModuleInterface $module);
}

