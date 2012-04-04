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
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class Translator implements \Nethgui\View\TranslatorInterface, \Nethgui\Utility\PhpConsumerInterface, \Nethgui\Log\LogConsumerInterface
{

    /**
     * @var \Nethgui\Utility\PhpWrapper
     */
    private $phpWrapper;

    /**
     * This is a stack of catalog names. Current catalog is the last element
     * of the array.
     * @var array
     */
    private $languageCatalogStack;
    private $catalogs = array();

    /**
     *
     * @var callable
     */
    private $catalogResolver;

    /**
     * 
     * @param string ISO 639-1 language code (2 characters)
     * @param callable $catalogResolver
     * @param array $initialCatalogStack 
     */
    public function __construct($languageCode, $catalogResolver, $initialCatalogStack = array())
    {
        if ( ! is_callable($catalogResolver)) {
            throw new \InvalidArgumentException(sprintf('%s: $catalogResolver must be a valid callback function.', get_class($this)), 1322240722);
        }

        $this->log = new \Nethgui\Log\Nullog();
        $this->phpWrapper = new \Nethgui\Utility\PhpWrapper();
        $this->languageCatalogStack = $initialCatalogStack;
        $this->defaultLanguage = $languageCode;
        $this->catalogResolver = $catalogResolver;
    }

    /**
     * Translate $string substituting $args
     *
     * Each key in array $args is searched and replaced in $string with
     * correspondent value.
     *
     * @see strtr()
     *
     * @param \Nethgui\Module\ModuleInterface $module
     * @param string $string The string to be translated
     * @param array $args Values substituted in output string.
     * @param string $languageCode The language code
     * @return string
     */
    public function translate(\Nethgui\Module\ModuleInterface $module, $string, $args = array(), $languageCode = NULL)
    {
        if ( ! is_string($string)) {
            throw new \InvalidArgumentException(sprintf("%s: in translate(); unexpected `%s` type!", get_class($this), gettype($string)), 1322150166);
        }

        if ( ! isset($languageCode)) {
            $languageCode = $this->getLanguageCode();
        }

        if (empty($languageCode)) {
            $translation = $string;
        } else {
            $catalogStack = $this->extractLanguageCatalogStack($module);
            $translation = $this->lookupTranslation($string, $languageCode, $catalogStack);
        }

        /**
         * Apply args to string
         */
        if (empty($args)) {
            return $translation;
        }

        /**
         * Automatically susbstitute numeric keys with ${N} placeholders.
         */
        $placeholders = array();
        foreach ($args as $argId => $argValue) {
            if (is_numeric($argId)) {
                $placeholders[sprintf('${%d}', $argId)] = $argValue;
            } else {
                $placeholders[$argId] = $argValue;
            }
        }

        return strtr($translation, $placeholders);
    }

    /**
     * @param string $key The string to be translated
     * @param string $languageCode The language code of the translated string
     * @param array $catalogStack The catalog stack where to start the search
     * @return string The translated string
     */
    private function lookupTranslation($key, $languageCode, $catalogStack)
    {
        $languageCatalogs = $this->languageCatalogStack;

        if ( ! empty($catalogStack)) {
            $languageCatalogs[] = $catalogStack;
        }

        $translation = NULL;
        $attempts = array();

        while (($catalog = array_pop($languageCatalogs)) !== NULL) {

            if (is_array($catalog)) {
                // push nested catalog stack elements
                $languageCatalogs = array_merge($languageCatalogs, $catalog);
                continue;
            }

            // If catalog is missing load it
            if ( ! isset($this->catalogs[$languageCode][$catalog])) {
                $this->loadLanguageCatalog($languageCode, $catalog);
            }

            // If key exists break
            if (isset($this->catalogs[$languageCode][$catalog][$key])) {
                $translation = $this->catalogs[$languageCode][$catalog][$key];
                break;
            } else {
                $attempts[] = $catalog;
            }
        }

        if ($translation === NULL) {
            // By default prepare an identity-translation
            $translation = $key;
            $this->getLog()->notice(sprintf("%s: `%s` translation not found for `%s`. Catalogs: [%s]", __CLASS__, $languageCode, $key, implode(', ', $attempts)));
        }

        return $translation;
    }

    private function loadLanguageCatalog($languageCode, $languageCatalog)
    {
        if (preg_match('/^[a-z][a-z]$/', $languageCode) == 0) {
            throw new \InvalidArgumentException(sprintf('%s: Language code must be a valid ISO 639-1 language code', get_class($this)), 1322150170);
        }
        if (preg_match('/^[a-z_A-Z0-9]+$/', $languageCatalog) == 0) {
            throw new \InvalidArgumentException(sprintf("%s: Language catalog name can contain only alphanumeric or `_` characters. It was `%s`.", get_class($this), $languageCatalog), 1322150265);
        }
        $prefix = \Nethgui\array_head(explode('_', $languageCatalog));

        $filePath = call_user_func($this->catalogResolver, sprintf('%s\Language\%s\%s', $prefix, $languageCode, $languageCatalog));
        $L = array();

        $included = @$this->phpWrapper->phpInclude($filePath, array('L' => &$L));
        if ($included) {
            //$this->getLog()->notice(sprintf('%s: Loaded language catalog `%s` [%s].', get_class($this), $languageCatalog, $languageCode));
        } else {
            $this->getLog()->warning(sprintf('%s: Missing language catalog `%s` [%s].', get_class($this), $languageCatalog, $languageCode));
        }
        $this->catalogs[$languageCode][$languageCatalog] = &$L;
    }

    private function extractLanguageCatalogStack(\Nethgui\Module\ModuleInterface $module)
    {
        $languageCatalogList = array();

        do {
            $catalog = $module->getAttributesProvider()->getLanguageCatalog();
            if (is_array($catalog)) {
                $languageCatalogList = array_merge($languageCatalogList, $catalog);
            } elseif (is_string($catalog)) {
                $languageCatalogList[] = $catalog;
            }
            $module = $module->getParent();
        } while ( ! is_null($module));

        return $languageCatalogList;
    }

    public function setPhpWrapper(\Nethgui\Utility\PhpWrapper $object)
    {
        $this->phpWrapper = $object;
    }

    public function getLog()
    {
        return $this->log;
    }

    public function setLog(\Nethgui\Log\LogInterface $log)
    {
        $this->log = $log;
        return $this;
    }

    public function getLanguageCode()
    {
        return $this->defaultLanguage;
    }

}

