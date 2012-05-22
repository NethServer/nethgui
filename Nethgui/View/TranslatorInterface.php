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
 * Translate strings by looking at the language catalogs provided by a module
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
interface TranslatorInterface
{

    /**
     * Translate a string
     * 
     * @api
     * @param ModuleInterface $module The module providing the language catalog(s)
     * @param string $string The string to be translated
     * @param array $args Hash of placeholders. Each key is replaced with the corresponding value. See PHP strtr()
     * @param string $languageCode The language of the returned string
     * @return string The translated string
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
