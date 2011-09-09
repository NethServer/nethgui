<?php
/**
 * @package Core
 */

/**
 * A Module implementing this interface provides to the framework the language
 * catalog name(s) where the translation strings are searched for.
 *
 * @see Nethgui_Core_ViewInterface
 * @author Davide Principi <davide.principi@nethesis.it>
 * @package Core
 */
interface Nethgui_Core_LanguageCatalogProvider {
    /**
     * The name of the language catalog where to search the translated strings
     * @return string|array The language catalog name, or catalog name list
     */
    public function getLanguageCatalog();
}