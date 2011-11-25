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
 * A ModuleSetInterface implementation contains all known modules.
 * 
 * It allows finding a module and iterating over root modules,
 * arranged in a hierarchical structure.
 *
 */
interface ModuleSetInterface
{

    /**
     * @return \RecursiveIterator A \RecursiveIterator to iterate over all accessible Modules
     */
    public function getModules();

    /**
     * @param string $moduleIdentifier
     * @return ModuleInterface
     */
    public function findModule($moduleIdentifier);

    /**
     * Declare that the given namespace is a Nethgui extension. It must have a "Module"
     * subpackage.
     *
     * For instance, a namespace should have the following directory/package structure
     *
     * <pre>
     * Acme
     *   Module
     *   Template
     *   Language
     *   Help
     * </pre>
     *
     * @param string $nsName
     * @param string $nsPath
     */
    public function registerNamespace($nsName, $nsPath);
}

