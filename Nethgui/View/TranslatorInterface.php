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
 * 
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
interface TranslatorInterface
{

    /**
     *
     * @api
     * @param ModuleInterface $module
     * @param string $string
     * @param array $args
     * @param string $languageCode
     * @return string
     */
    public function translate(\Nethgui\Module\ModuleInterface $module, $string, $args = array(), $languageCode = NULL);

    /**
     * Get the default language code
     *
     * @api
     * @return string ISO 639-1 language code (2 characters).
     */
    public function getLanguageCode();
}
