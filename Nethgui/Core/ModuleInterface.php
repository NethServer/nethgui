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
 * Core module operations
 *
 * A module interface implementation is delegated to
 * - initialize the module (and its submodules)
 * - prepare view parameters
 * - provide module informations
 *
 * @see RequestHandlerInterface
 */
interface ModuleInterface
{
    /**
     * Prepare the server view
     */
    const VIEW_SERVER = 0;
    /**
     * Prepare the client view
     */
    const VIEW_CLIENT = 1;
    /**
     * Prepare an help template
     */
    const VIEW_HELP = 2;


    /**
     * Sets the host configuration Model.
     */
    public function setPlatform(\Nethgui\System\PlatformInterface $platform);

    /**
     * The Module Identifier is a string that univocally identifies a Module.
     * @return string Returns the unique module identifier
     */
    public function getIdentifier();

    /**
     * Sets the reference to the parent Module.
     * @see ModuleCompositeInterface addChild() operation.
     */
    public function setParent(ModuleInterface $parentModule);

    /**
     * Gets a reference to the parent Module.
     * @return ModuleInterface
     */
    public function getParent();

    /**
     * Gets the Module ``title`` attribute.
     * @return string
     */
    public function getTitle();

    /**
     * Gets the Module ``description`` attribute.
     * @return string
     */
    public function getDescription();

    /**
     * After initialization a module...
     */
    public function initialize();

    /**
     * Prevents double initialization.
     * @return bool FALSE, if not yet initialized, TRUE otherwise.
     */
    public function isInitialized();

    /**
     * Prepare view layer data, putting it into $view.
     *
     * @param ViewInterface $view The view to put the data into
     * @param integer $mode One of VIEW_CLIENT or VIEW_SERVER values
     * @see ModuleInterface::VIEW_SERVER
     * @see ModuleInterface::VIEW_CLIENT
     * @see ViewInterface
     */
    public function prepareView(ViewInterface $view, $mode);


    /**
     * Get module tags for search implementation. Any composite module must take care of getTags children's call.
     * @return array in the form (urlModule, (tag1,tag2..tagn)) rappresenting search tags foreach module 
     */
    public function getTags();
}

/**
 * A "Top" Module is a Module displayed on the application main menu.
 * 
 * If it provides the identifier of another Module, it will be placed
 * under that Module.
 *
 * Also a "Top" Module is reachable with a specific URL, unlike other Modules.
 *
 *
 */
interface TopModuleInterface
{

    /**
     * @return string Unique parent module identifier
     */
    public function getParentMenuIdentifier();
}

