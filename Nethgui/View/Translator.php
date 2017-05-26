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
 * Search for a string translation looking to all the language catalogs provided
 * by a hierarchy of modules.
 * 
 * The string lookup procedes up to the root of the hierarchy by invoking 
 * the module getParent() method.
 * 
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class Translator implements \Nethgui\View\TranslatorInterface, \Nethgui\Utility\PhpConsumerInterface, \Nethgui\Log\LogConsumerInterface
{

    /**
     * @var \Nethgui\Utility\PhpWrapper
     */
    private $phpWrapper;

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
    public function __construct($languageCode, $catalogResolver, $notUsed = array())
    {
        if ( ! is_callable($catalogResolver)) {
            throw new \InvalidArgumentException(sprintf('%s: $catalogResolver must be a valid callback function.', get_class($this)), 1322240722);
        }

        $this->log = new \Nethgui\Log\Nullog();
        $this->phpWrapper = new \Nethgui\Utility\PhpWrapper();
        $this->defaultLanguage = $languageCode;
        $this->catalogResolver = $catalogResolver;
    }

    /**
     * Translate $key substituting $args
     *
     * Any occurence of "${ID}" in the translated string is
     * substituted by the value in $args corresponding to 
     * index "ID".
     *
     * @see strtr()
     * 
     * @param \Nethgui\Module\ModuleInterface $module
     * @param string $key The string to be translated
     * @param array $args Values substituted in output string.
     * @param string $languageCode The language code
     * @return string
     */
    public function translate(\Nethgui\Module\ModuleInterface $module, $key, $args = array(), $languageCode = NULL)
    {
        if ( ! isset($languageCode)) {
            $languageCode = $this->getLanguageCode();
        }

        if (empty($languageCode)) {
            $translation = $key;
        } else {
            $catalogStack = $this->extractLanguageCatalogStack($module);
            $translation = $this->lookupTranslation((string) $key, $languageCode, $catalogStack);
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
            $placeholders[sprintf('${%s}', $argId)] = $argValue;
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
        $languageCatalogs = $catalogStack;
        $translation = NULL;
        $attempts = array();

        while (($catalog = array_shift($languageCatalogs)) !== NULL) {

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
            if ($languageCode === 'en' || NETHGUI_DEBUG === TRUE) {
                // By default prepare an identity-translation
                $translation = $key;
                NETHGUI_DEBUG && $this->getLog()->notice(sprintf("%s: `%s` translation not found for `%s`. Catalogs: [%s]",
                            __CLASS__, $languageCode, $key,
                            implode(', ', $attempts)));
            } else {
                return $this->lookupTranslation($key, 'en', $catalogStack);
            }
        }

        return $translation;
    }

    private function loadLanguageCatalog($languageCode, $languageCatalog)
    {
        if (preg_match('/^[a-z][a-z]([-_][A-Z][A-Z])?$/', $languageCode) == 0) {
            throw new \InvalidArgumentException(sprintf('%s: Language code must be an IETF-like language tag', get_class($this)), 1458301359);
        }
        if (preg_match('/^[a-z_A-Z0-9]+$/', $languageCatalog) == 0) {
            throw new \InvalidArgumentException(sprintf("%s: Language catalog name can contain only alphanumeric or `_` characters. It was `%s`.", get_class($this), $languageCatalog), 1322150265);
        }

        $filePath = call_user_func($this->catalogResolver, $languageCode, $languageCatalog);
        $L = array();

        $tmp = $this->getLog()->getLevel();
        $this->getLog()->setLevel($tmp & ~E_WARNING);
        $included = $this->phpWrapper->phpInclude($filePath, array('L' => &$L));
        $this->getLog()->setLevel($tmp);
        if ($included) {
            NETHGUI_DEBUG && $this->getLog()->notice(sprintf('%s: Loaded language catalog `%s` [%s].', get_class($this), $languageCatalog, $languageCode));
        } else {
            NETHGUI_DEBUG && $this->getLog()->warning(sprintf('%s: Missing language catalog `%s` [%s].', get_class($this), $languageCatalog, $languageCode));
        }
        $this->catalogs[$languageCode][$languageCatalog] = &$L;
    }

    private function extractLanguageCatalogStack(\Nethgui\Module\ModuleInterface $module)
    {
        $languageCatalogList = array();

        $moduleNamespace = \Nethgui\array_head(explode("\\", get_class($module)));
        $defaultNamespace = \Nethgui\array_head(explode("\\", get_class($this)));

        do {
            $catalog = $module->getAttributesProvider()->getLanguageCatalog();
            if (is_array($catalog)) {
                $languageCatalogList = array_merge($languageCatalogList, $catalog);
            } elseif (is_string($catalog)) {
                $languageCatalogList[] = $catalog;
            }
            $module = $module->getParent();
        } while ( ! is_null($module));

        // Append namespace catalogs at the end of the list:
        $languageCatalogList = array_unique(array_merge($languageCatalogList, array($moduleNamespace, $defaultNamespace)));

        return $languageCatalogList;
    }

    public function setPhpWrapper(\Nethgui\Utility\PhpWrapper $object)
    {
        $this->phpWrapper = $object;
        return $this;
    }

    public function getLog()
    {
        return $this->log;
    }

    public function setLog(\Nethgui\Log\LogInterface $log)
    {
        $this->log = $log;
        $this->phpWrapper->setLog($log);
        return $this;
    }

    public function getLanguageCode()
    {
        return $this->defaultLanguage;
    }

}

