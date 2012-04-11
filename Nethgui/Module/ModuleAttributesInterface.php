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
 * All values returned from these operations are invariants.
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
interface ModuleAttributesInterface
{
    /**
     * Gets the Module title
     * 
     * @api
     * 
     * @return string
     */
    public function getTitle();

    /**
     * Gets the Module description
     *
     * @api
     * 
     * @return string
     */
    public function getDescription();

    /**
     * Gets module tags.
     *
     * Any composite module must take care of getTags children's call.
     *
     * Tags are used to search a module among the others
     *
     * @api
     * 
     * @return string
     */
    public function getTags();

    /**
     * The category of the aggregated module
     *
     * @api
     * 
     * @return string Unique parent module identifier
     */
    public function getCategory();
    
    /**
     * The menu position of the aggregated module
     * 
     * @api
     * 
     * @return string
     */
    public function getMenuPosition();

    /**
     * The name of the language catalog where to search the translated strings
     * 
     * @api
     * 
     * @return string|array The language catalog name, or catalog name list
     */
    public function getLanguageCatalog();
}



