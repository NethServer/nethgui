<?php namespace Nethgui\Module;

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
class Help extends \Nethgui\Controller\CompositeController implements \Nethgui\Component\DependencyConsumer
{
    /**
     *
     * @var \Nethgui\Module\ModuleSetInterface
     */
    private $moduleSet;

    /**
     *
     * @param \Nethgui\Module\ModuleSetInterface $moduleSet
     * @return Menu
     */
    public function setModuleSet(\Nethgui\Module\ModuleSetInterface $moduleSet)
    {
        $this->moduleSet = $moduleSet;
        return $this;
    }

    public function getModuleSet()
    {
        return $this->moduleSet;
    }

    public function setFileNameResolver($fileNameResolver)
    {
        $this->fileNameResolver = $fileNameResolver;
        return $this;
    }

    public function initialize()
    {
        parent::initialize();
        $this->addChild(new \Nethgui\Module\Help\Show());
        $this->addChild(new \Nethgui\Module\Help\Template());
        $this->addChild(new \Nethgui\Module\Help\Read());
        foreach ($this->getChildren() as $child) {
            if ($child instanceof Help\Common) {
                $child->setFileNameResolver($this->fileNameResolver);
            }
        }
    }

    public function getDependencySetters()
    {
        return array(
            'FilenameResolver' => array($this, 'setFileNameResolver'),
            'ModuleSet' => array($this, 'setModuleSet'),
        );
    }

}