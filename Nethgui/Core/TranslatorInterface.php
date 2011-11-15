<?php
/**
 * @package Language
 */

/**
 * @package Language
 * @ignore
 * @author Davide Principi <davide.principi@nethesis.it>
 */
interface Nethgui_Core_TranslatorInterface
{

    /**
     * @param Nethgui_Core_ModuleInterface $module
     * @param string $string
     * @param array $args
     * @param string $languageCode
     * @return string
     */
    public function translate(Nethgui_Core_ModuleInterface $module, $string, $args = array(), $languageCode = NULL);

    /**
     * Get the default language code
     * @return string ISO 639-1 language code (2 characters).
     */
    public function getLanguageCode();
}