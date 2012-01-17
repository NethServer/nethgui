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
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class SimpleModuleAttributesProvider implements \Nethgui\Module\ModuleAttributesInterface
{

    protected $title, $category, $description, $languageCatalog, $tags, $menuPosition;

    /**
     * Create a new instance from basic module informations
     * 
     * @param \Nethgui\Module\ModuleInterface $module
     * @return SimpleModuleAttributesProvider 
     */
    public function initializeFromModule(\Nethgui\Module\ModuleInterface $module)
    {
        $i = $module->getIdentifier();
        $this->title = $i . '_Title';
        $this->description = $i . '_Description';
        $this->languageCatalog = strtr(get_class($module), '\\', '_');
        $this->tags = $i . '_Tags';

        return $this;
    }

    /**
     * Set category and menuPosition values
     * 
     * @param \Nethgui\Module\ModuleAttributesInterface $base
     * @param string $category
     * @param string $menuPosition
     * @return SimpleModuleAttributesProvider
     */
    public static function extendModuleAttributes(\Nethgui\Module\ModuleAttributesInterface $base, $category = NULL, $menuPosition = NULL)
    {
        $o = new static();

        $o->title = $base->getTitle();
        $o->description = $base->getDescription();
        $o->languageCatalog = $base->getLanguageCatalog();
        $o->tags = $base->getTags();
        $o->category = $category;
        $o->menuPosition = is_numeric($menuPosition) ? sprintf('%05d', $menuPosition) : strval($menuPosition);

        return $o;
    }


    public function getCategory()
    {
        return $this->category;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getLanguageCatalog()
    {
        return $this->languageCatalog;
    }

    public function getMenuPosition()
    {
        return $this->menuPosition;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function getTitle()
    {
        return $this->title;
    }

}
