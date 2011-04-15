<?php
/**
 * @package Core
 */

/**
 * A Module implementing this interface provides to the framework the language
 * catalog name where the translated strings are taken.
 *
 * @see NethGui_Core_ViewInterface
 * @author Davide Principi <davide.principi@nethesis.it>
 * @package Core
 */
interface NethGui_Core_LanguageCatalogProvider {
    /**
     * The name of the language catalog where to search the translated strings
     * @return string
     */
    public function getLanguageCatalog();
}