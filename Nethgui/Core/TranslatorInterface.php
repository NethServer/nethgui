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
     * @param string $code
     */
    public function setLanguageCode($code);

    /**
     * @return string
     */
    public function getLanguageCode();

    /**
     * @return string
     */
    public function getDateFormat();
}