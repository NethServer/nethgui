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
 * Core module operations
 *
 * A module interface implementation is delegated to
 * - initialize the module (and its submodules)
 * - prepare view parameters
 *
 * @see \Nethgui\Controller\RequestHandlerInterface
 * @since 1.0
 * @api
 */
interface ModuleInterface
{

    /**
     * The Module Identifier is a string that univocally identifies a Module.
     *
     * @return string Returns the unique module identifier
     * @api
     */
    public function getIdentifier();

    /**
     * Sets the reference to the parent Module.
     *
     * @see ModuleCompositeInterface addChild() operation.
     * @return ModuleInterface
     * @api
     */
    public function setParent(ModuleInterface $parentModule);

    /**
     * Gets a reference to the parent Module.
     *
     * @return ModuleInterface
     * @api
     */
    public function getParent();

    /**
     * After initialization a module...
     *
     * @return void
     * @api
     */
    public function initialize();

    /**
     * Prevents double initialization.
     *
     * @return bool FALSE, if not yet initialized, TRUE otherwise.
     * @api
     */
    public function isInitialized();

    /**
     * Gain access to the attributes of this module
     *
     * @return ModuleAttributesInterface
     * @api
     */
    public function getAttributesProvider();
}

