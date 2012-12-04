<?php
namespace Nethgui\Module;

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
 * Create module instances from a given path 
 * 
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0 
 */
class ModuleLoader implements \Nethgui\Module\ModuleSetInterface, \Nethgui\Utility\PhpConsumerInterface, \Nethgui\Log\LogConsumerInterface
{
    /**
     * @var \ArrayObject
     */
    private $namespaceMap;

    /**
     *
     * @var ArrayObject
     */
    private $instanceCache;

    /**
     *
     * @var GlobalFunctionWrapper
     */
    private $phpWrapper;

    /**
     *
     * @var array
     */
    private $onInstantiate = array();

    public function __construct()
    {
        $this->namespaceMap = new \ArrayObject();
        $this->instanceCache = new \ArrayObject();
        $this->phpWrapper = new \Nethgui\Utility\PhpWrapper(__CLASS__);
        $this->cacheIsFilled = FALSE;
    }

    /**
     *
     * @param string $nsPrefix
     * @param string $nsRootPath 
     * @return \Nethgui\Module\ModuleLoader
     */
    public function setNamespace($nsPrefix, $nsRootPath)
    {
        $this->namespaceMap[$nsPrefix] = $nsRootPath;
        return $this;
    }

    public function getIterator()
    {
        if ($this->cacheIsFilled !== TRUE) {
            $this->fillCache();
        }
        return $this->instanceCache->getIterator();
    }

    private function fillCache()
    {
        foreach ($this->namespaceMap as $nsPrefix => $nsRootPath) {
            if ($nsRootPath === FALSE) {
                continue;
            }

            $path = $nsRootPath . '/' . str_replace('\\', '/', $nsPrefix);

            $files = $this->phpWrapper->scandir($path);

            if ($files === FALSE) {
                throw new \UnexpectedValueException(sprintf("%s: `%s` is not a valid module directory!", get_class($this), $path), 1322649822);
            }

            foreach ($files as $fileName) {
                if (substr($fileName, -4) !== '.php') {
                    continue;
                }

                $moduleIdentifier = substr($fileName, 0, -4);

                if ( ! isset($this->instanceCache[$moduleIdentifier])) {
                    $className = $nsPrefix . '\\' . $moduleIdentifier;
                    $moduleInstance = new $className();
                    NETHGUI_DEBUG && $this->getLog()->notice(sprintf('%s::fillCache(): Created "%s" instance', get_class($this), $className));
                    $this->notifyCallbacks($moduleInstance);
                    $this->instanceCache[$moduleIdentifier] = $moduleInstance;
                }
            }
        }

        $this->cacheIsFilled = TRUE;
    }

    public function getModule($moduleIdentifier)
    {

        // Module is already instantiated, return it:
        if (isset($this->instanceCache[$moduleIdentifier])) {
            return $this->instanceCache[$moduleIdentifier];
        }

        $nsPrefixList = array_keys(iterator_to_array($this->namespaceMap));

        $moduleInstance = NULL;

        // Resolve module class namespaces LIFO
        while ($nsPrefix = array_pop($nsPrefixList)) {
            $className = $nsPrefix . '\\' . $moduleIdentifier;

            if ($this->phpWrapper->class_exists($className)) {
                $moduleInstance = new $className();
                NETHGUI_DEBUG && $this->getLog()->notice(sprintf('%s::getModule(): Created "%s" instance', get_class($this), $className));
                $this->notifyCallbacks($moduleInstance);
                $this->instanceCache[$moduleIdentifier] = $moduleInstance;
                break;
            }
        }

        if ($moduleInstance === NULL) {
            throw new \RuntimeException(sprintf("%s: `%s` is an unknown module identifier", __CLASS__, $moduleIdentifier), 1322231262);
        }

        return $moduleInstance;
    }

    public function setPhpWrapper(\Nethgui\Utility\PhpWrapper $object)
    {
        $this->phpWrapper = $object;
        return $this;
    }

    public function getLog()
    {
        return $this->phpWrapper->getLog();
    }

    public function setLog(\Nethgui\Log\LogInterface $log)
    {
        $this->phpWrapper->setLog($log);
        return $this;
    }

    /**
     *
     * @param callable $callable
     * @return ModuleLoader
     */
    public function addInstantiateCallback($callable)
    {
        $this->onInstantiate[] = $callable;
        return $this;
    }

    private function notifyCallbacks(ModuleInterface $module)
    {
        foreach ($this->onInstantiate as $callback) {
            call_user_func($callback, $module);
        }
    }

}