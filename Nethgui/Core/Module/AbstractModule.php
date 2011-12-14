<?php
namespace Nethgui\Core\Module;

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

use Nethgui\Core\ModuleInterface;
use Nethgui\Core\ModuleDescriptorInterface;
use Nethgui\Log\LogConsumerInterface;

/**
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
abstract class AbstractModule implements ModuleInterface, LogConsumerInterface, DefaultUiStateInterface
{

    /**
     * @var string
     */
    private $identifier;

    /**
     *
     * @var ModuleInterface;
     */
    private $parent;
    /*
     * @var bool
     */
    private $initialized = FALSE;

    /**
     * @var \Nethgui\System\PlatformInterface
     */
    private $platform;

    /**
     *
     * @var \Nethgui\Log\LogInterface
     */
    private $log;

    /**
     *
     * @var \Nethgui\Core\SimpleModuleAttributesProvider
     */
    private $descriptor;

    /**
     * Template applied to view, if different from NULL
     *
     * @see \Nethgui\Core\ViewInterface::setTemplate()
     * @var string|callable
     */
    private $viewTemplate;

    public function __construct($identifier = NULL)
    {
        $this->viewTemplate = NULL;
        if (isset($identifier)) {
            $this->identifier = $identifier;
        } else {
            $this->identifier = \Nethgui\array_end(explode('\\', get_class($this)));
        }
    }

    public function setPlatform(\Nethgui\System\PlatformInterface $platform)
    {
        $this->platform = $platform;

        if (is_null($this->log) && $platform instanceof LogConsumerInterface) {
            $log = $platform->getLog();
            if ($log instanceof \Nethgui\Log\LogInterface) {
                $this->setLog($log);
            }
        }

        return $this;
    }

    /**
     * @return \Nethgui\System\PlatformInterface
     */
    protected function getPlatform()
    {
        return $this->platform;
    }

    /**
     *  Overriding methods can read current state from model.
     */
    public function initialize()
    {
        if ($this->initialized === FALSE) {
            $this->initialized = TRUE;
        } else {
            throw new \Exception("Double Module initialization is forbidden.", 1322148737);
        }
    }

    public function isInitialized()
    {
        return $this->initialized;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setParent(\Nethgui\Core\ModuleInterface $parentModule)
    {
        $this->parent = $parentModule;
        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function prepareView(\Nethgui\Core\ViewInterface $view, $mode)
    {
        $template = $this->getViewTemplate();
        if ( ! is_null($template)) {
            $view->setTemplate($template);
        }
    }

    protected function setViewTemplate($template)
    {
        $this->viewTemplate = $template;
        return $this;
    }

    protected function getViewTemplate()
    {
        return $this->viewTemplate;
    }

    public function setLog(\Nethgui\Log\LogInterface $log)
    {
        $this->log = $log;
    }

    public function getLog()
    {
        if ( ! isset($this->log)) {
            return new \Nethgui\Log\Nullog;
        }
        return $this->log;
    }

    /**
     * Called after a default attributes provider object is created
     *
     * @see getAttributesProvider()
     * @param \Nethgui\Core\ModuleAttributesInterface $descriptor
     * @return \Nethgui\Core\ModuleAttributesInterface
     */
    protected function initializeAttributes(\Nethgui\Core\ModuleAttributesInterface $attributes)
    {
        return $attributes;
    }

    public function getAttributesProvider()
    {
        if ( ! isset($this->descriptor)) {
            $attributes = new \Nethgui\Core\SimpleModuleAttributesProvider();
            $this->descriptor = $this->initializeAttributes($attributes->initializeFromModule($this));
        }
        return $this->descriptor;
    }

    public function getDefaultUiStyleFlags()
    {
        return 0;
    }

}
