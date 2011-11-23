<?php
/**
 * @package Language
 */

namespace Nethgui\Language;

/**
 * @package Language
 * @ignore
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class Translator implements \Nethgui\Core\TranslatorInterface, \Nethgui\Core\GlobalFunctionConsumer, \Nethgui\Log\LogConsumerInterface
{

    /**
     * @var \Nethgui\Core\GlobalFunctionWrapper
     */
    private $globalFunctionWrapper;
    
    /**
     * This is a stack of catalog names. Current catalog is the last element
     * of the array.
     * @var array
     */
    private $languageCatalogStack;
    private $catalogs = array();

    /**
     *
     * @var \Nethgui\Client\UserInterface
     */
    private $user;

    /**
     * 
     * @param \Nethgui\Client\UserInterface $user
     * @param \Nethgui\Log\AbstractLog $log
     */
    public function __construct(\Nethgui\Client\UserInterface $user, \Nethgui\Log\AbstractLog $log)
    {
        $this->globalFunctionWrapper = new \Nethgui\Core\GlobalFunctionWrapper();        
        $this->languageCatalogStack = array('\Nethgui\Framework', NETHGUI_APPLICATION);
        $this->log = $log;
        $this->user = $user;
    }

    /**
     * Translate $string substituting $args
     *
     * Each key in array $args is searched and replaced in $string with
     * correspondent value.
     *
     * @see strtr()
     *
     * @param \Nethgui\Core\ModuleInterface $module
     * @param string $string The string to be translated
     * @param array $args Values substituted in output string.
     * @param string $languageCode The language code
     * @return string
     */
    public function translate(\Nethgui\Core\ModuleInterface $module, $string, $args = array(), $languageCode = NULL)
    {
        if ( ! is_string($string)) {
            throw new InvalidArgumentException(sprintf("translate(): unexpected `%s` type!", gettype($string)));
        }

        if ( ! isset($languageCode)) {
            $languageCode = $this->user->getLanguageCode();
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
            if (NETHGUI_ENVIRONMENT == 'development') {
                $this->getLog()->warning("Missing `$languageCode` translation for `$key`. Catalogs: " . implode(', ', $attempts), 'debug');
            }
        }

        return $translation;
    }

    private function loadLanguageCatalog($languageCode, $languageCatalog)
    {
        if (preg_match('/[a-z][a-z]/', $languageCode) == 0) {
            throw new InvalidArgumentException('Language code must be a valid ISO 639-1 language code');
        }
        if (preg_match('/[a-z_A-Z0-9]+/', $languageCatalog) == 0) {
            throw new InvalidArgumentException("Language catalog name can contain only alphanumeric or `_` characters. It was `$languageCatalog`.");
        }
        $prefix = array_shift(explode('_', $languageCatalog));
        $filePath = NETHGUI_ROOTDIR . '/' . $prefix . '/Language/' . $languageCode . '/' . $languageCatalog . '.php';
        $L = array();

        $included = @$this->globalFunctionWrapper->phpInclude($filePath, array('L' => &$L));
        if ($included) {
            $this->getLog()->notice(sprintf('Loaded catalog %s (%s)', $languageCatalog, $languageCode));
        } else {
             $this->getLog()->notice(sprintf('Missing catalog %s (%s)', $languageCatalog, $languageCode));
        }
        $this->catalogs[$languageCode][$languageCatalog] = &$L;
    }


    private function extractLanguageCatalogStack(\Nethgui\Core\ModuleInterface $module)
    {
        $languageCatalogList = array();

        do {
            if ($module instanceof \Nethgui\Core\LanguageCatalogProvider) {
                $catalog = $module->getLanguageCatalog();
                if (is_array($catalog)) {
                    $languageCatalogList = array_merge($languageCatalogList, $catalog);
                } elseif (is_string($catalog)) {
                    $languageCatalogList[] = $catalog;
                }
            }

            $module = $module->getParent();
        } while ( ! is_null($module));

        return $languageCatalogList;
    }

    public function setGlobalFunctionWrapper(\Nethgui\Core\GlobalFunctionWrapper $object)
    {
        $this->globalFunctionWrapper = $object;
    }

    public function getLog()
    {
        return $this->log;
    }

    public function setLog(\Nethgui\Log\AbstractLog $log)
    {
        $this->log = $log;
        return $this;
    }

    public function getLanguageCode()
    {
        return $this->user->getLanguageCode();
    }

}

