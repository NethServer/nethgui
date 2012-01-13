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
 */
class ModuleSurrogate implements \Nethgui\Core\ModuleInterface, \Serializable
{

    private $info;

    public function __construct(\Nethgui\Core\ModuleInterface $originalModule)
    {
        $this->info = array();

        $this->info['getIdentifier'] = $originalModule->getIdentifier();
        $this->info['getAttributesProvider'] = $originalModule->getAttributesProvider();

        $parent = $originalModule->getParent();
        if ($parent instanceof \Nethgui\Core\ModuleInterface) {
            $this->info['getParent'] = new self($parent);
        } else {
            $this->info['getParent'] = NULL;
        }
    }

    public function getAttributesProvider()
    {
        return $this->info['getAttributesProvider'];
    }

    public function getIdentifier()
    {
        return $this->info['getIdentifier'];
    }

    public function getParent()
    {
        return $this->info['getParent'];
    }

    public function initialize()
    {
        throw new \Exception('Not implemented ' . __METHOD__, 1323096813);
    }

    public function isInitialized()
    {
        throw new \Exception('Not implemented ' . __METHOD__, 1323096814);
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        throw new \Exception('Not implemented ' . __METHOD__, 1323096815);
    }

    public function setPlatform(\Nethgui\System\PlatformInterface $platform)
    {
        throw new \Exception('Not implemented ' . __METHOD__, 1323096816);
    }

    public function setParent(\Nethgui\Core\ModuleInterface $parentModule)
    {
        throw new \Exception('Not implemented ' . __METHOD__, 1323096817);
    }

    public function serialize()
    {
        return serialize($this->info);
    }

    public function unserialize($serialized)
    {
        $this->info = unserialize($serialized);
    }

}
