<?php
namespace Nethgui\Client;

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
 * A view object holds output data and references to child views.
 * 
 * It inherits an ArrayAccess interface and is capable to convert its internal
 * state to a string using a Template.
 *
 * A Template can be a PHP script or a callback function that receives the
 * view state.
 *
 * Usually, PHP templates are kept into View/ directories, but generally they
 * follow the class naming convention.
 *
 * Moreover, every module has a View object assigned to it as a parameter during
 * prepareView() operation.
 *
 * @see \Nethgui\Core\ModuleInterface::prepareView()
 */
class View implements \Nethgui\Core\ViewInterface, \Nethgui\Log\LogConsumerInterface
{

    /**
     * Reference to associated module
     * @var \Nethgui\Core\ModuleInterface
     */
    private $module;

    /**
     * Holds view state
     * @var array
     */
    private $data;

    /**
     *
     * @var string
     */
    private $template;

    /**
     * Caches the identifier of all ancestors from the root to the
     * associated $module.
     * @var array
     */
    private $modulePath;

    /**
     *
     * @var \Nethgui\Core\TranslatorInterface;
     */
    private $translator;

    /**
     *
     * @var string
     */
    private $siteUrl;

    /**
     *
     * @var string
     */
    private $controllerPath;

    /**
     * @see getPathUrl()
     * @var string
     */
    private $pathUrl;

    public function __construct(\Nethgui\Core\ModuleInterface $module, \Nethgui\Core\TranslatorInterface $translator, $siteUrl = '', $pathUrl = '/', $controllerPath = '')
    {
        $this->module = $module;
        $this->translator = $translator;
        $this->siteUrl = $siteUrl;
        $this->controllerPath = $controllerPath;
        $this->pathUrl = $pathUrl;
        $this->template = str_replace('\Module\\', '\Template\\', get_class($module));
        $this->data = array();
    }

    public function copyFrom($data)
    {
        foreach ($data as $offset => $value) {
            $this->offsetSet($offset, $value);
        }
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function spawnView(\Nethgui\Core\ModuleInterface $module, $register = FALSE)
    {
        $spawnedView = new self($module, $this->translator, $this->siteUrl, $this->pathUrl, $this->controllerPath);
        if ($register === TRUE) {
            $this[$module->getIdentifier()] = $spawnedView;
        } elseif (is_string($register)) {
            $this[$register] = $spawnedView;
        }
        return $spawnedView;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->data[$offset];
        }

        return NULL;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function translate($value, $args = array())
    {
        return $this->translator->translate($this->getModule(), $value, $args);
    }

    public function getTranslator()
    {
        return $this->translator;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function getModulePath()
    {
        if ( ! isset($this->modulePath)) {
            $this->modulePath = array();

            $watchdog = 0;
            $module = $this->getModule();

            while ( ! (is_null($module))) {
                if ( ++ $watchdog > 20) {
                    throw new \LogicException(sprintf("%s: Too many nested modules or cyclic module structure.", get_class($this)), 1322150445);
                }
                array_unshift($this->modulePath, $module->getIdentifier());
                $module = $module->getParent();
            }
        }

        return $this->modulePath;
    }

    public function resolvePath($path)
    {
        if (is_array($path) || is_object($path)) {
            throw new \InvalidArgumentException(sprintf('%s: $path argument must be a string, `%s` given.', get_class($this), gettype($path)), 1322150499);
        }

        $path = strval($path);

        if (strlen($path) > 0 && $path[0] == '/') {
            // if the first character is a / consider an absolute path
            $pathSegments = array();
        } else {
            // else consider a path relative to the current module
            $pathSegments = $this->getModulePath();
        }

        foreach (explode('/', $path) as $part) {
            if ($part == '' || $part == '.') {
                continue; // skip empty parts
            } elseif ($part == '..') {
                array_pop($pathSegments); // backreference
            } else {
                $pathSegments[] = $part; // add segment
            }
        }

        return $pathSegments;
    }

    public function getUniqueId($path = '')
    {
        return implode('_', $this->resolvePath($path));
    }

    public function getClientEventTarget($name)
    {
        if (NETHGUI_ENABLE_TARGET_HASH === FALSE) {
            return $this->getUniqueId($name);
        }
        return substr(md5($this->getUniqueId($name)), 0, 8);
    }

    /**
     * @param string $path
     * @param array $parameters
     */
    private function buildUrl($path, $parameters = array())
    {
        if (is_array($path) || is_object($path)) {
            throw new \InvalidArgumentException(sprintf('%s: $path argument must be a string, `%s` given.', get_class($this), gettype($path)), 1322150500);
        }

        $path = strval($path);
        $fragment = '';

        if (strpos($path, '#') !== FALSE) {
            list($path, $fragment) = explode('#', $path, 2);

            $fragment = '#' . $fragment;
        }

        $segments = $this->resolvePath($path);

        if ($this->controllerPath !== '') {
            array_unshift($segments, $this->controllerPath);
        }

        $url = $this->pathUrl . '/' . implode('/', $segments);

        if ( ! empty($parameters)) {
            $url .= '?' . http_build_query($parameters);
        } 

        return $url . $fragment;
    }

    public function getModuleUrl($path = '')
    {
        return $this->buildUrl($path);
    }

    public function getSiteUrl()
    {
        return $this->siteUrl;
    }

    public function getPathUrl()
    {
        return $this->pathUrl;
    }

    public function setLog(\Nethgui\Log\AbstractLog $log)
    {
        throw new \LogicException(sprintf('Cannot invoke setLog() on %s', get_class($this)));
    }

    public function getLog()
    {
        if ($this->getModule() instanceof \Nethgui\Log\LogConsumerInterface) {
            return $this->getModule()->getLog();
        } elseif ($this->translator instanceof \Nethgui\Log\LogConsumerInterface) {
            return $this->translator->getLog();
        } else {
            return new \Nethgui\Log\Nullog();
        }
    }

    public function createUiCommand($methodName, $arguments)
    {
        return new Command($methodName, $arguments);
    }

}

