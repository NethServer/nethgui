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
 * A Module surrogate is employed to store module informations into the User session,
 * during DialogBox serialization.
 * 
 * @see DialogBox
 * @ignore
 */
class ModuleSurrogate implements \Nethgui\Core\ModuleInterface, \Nethgui\Core\LanguageCatalogProvider, Serializable
{

    private $info;

    public function __construct(\Nethgui\Core\ModuleInterface $originalModule)
    {
        $this->info = array();

        $this->info['getIdentifier'] = $originalModule->getIdentifier();
        $this->info['getTitle'] = $originalModule->getTitle();
        $this->info['getDescription'] = $originalModule->getDescription();
        $this->info['getLanguageCatalog'] = $originalModule->getLanguageCatalog();

        $parent = $originalModule->getParent();
        if ($parent instanceof \Nethgui\Core\ModuleInterface) {
            $this->info['getParent'] = new self($parent);
        } else {
            $this->info['getParent'] = NULL;
        }
    }

    public function getDescription()
    {
        return $this->info['getDescription'];
    }

    public function getIdentifier()
    {
        return $this->info['getIdentifier'];
    }

    public function getParent()
    {
        return $this->info['getParent'];
    }

    public function getTitle()
    {
        return $this->info['getTitle'];
    }

    public function getLanguageCatalog()
    {
        return $this->info['getLanguageCatalog'];
    }

    public function initialize()
    {
        throw new Exception('Not implemented ' . __METHOD__);
    }

    public function isInitialized()
    {
        throw new Exception('Not implemented ' . __METHOD__);
    }

    public function prepareView(\Nethgui\Core\ViewInterface $view, $mode)
    {
        throw new Exception('Not implemented ' . __METHOD__);
    }

    public function setPlatform(\Nethgui\System\PlatformInterface $platform)
    {
        throw new Exception('Not implemented ' . __METHOD__);
    }

    public function setParent(\Nethgui\Core\ModuleInterface $parentModule)
    {
        throw new Exception('Not implemented ' . __METHOD__);
    }

    public function serialize()
    {
        return serialize($this->info);
    }

    public function unserialize($serialized)
    {
        $this->info = unserialize($serialized);
    }

    public function getTags()
    {
        return array();
    }

}
