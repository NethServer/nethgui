<?php
/**
 */

namespace Nethgui\Core;

/**
 * A Module implementing this interface provides to the framework the language
 * catalog name(s) where the translation strings are searched for.
 *
 * @see ViewInterface
 * @author Davide Principi <davide.principi@nethesis.it>
 */
interface LanguageCatalogProvider {
    /**
     * The name of the language catalog where to search the translated strings
     * @return string|array The language catalog name, or catalog name list
     */
    public function getLanguageCatalog();
}
