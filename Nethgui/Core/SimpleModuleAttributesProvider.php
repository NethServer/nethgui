<?php
namespace Nethgui\Core;

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
 *
 * @api
 */
class SimpleModuleAttributesProvider implements ModuleAttributesInterface
{

    private $title, $category, $description, $languageCatalog, $tags, $menuPosition;

    public function __construct(ModuleInterface $module, $category = NULL, $menuPosition = NULL)
    {
        $i = $module->getIdentifier();
        $this->category = $category;
        $this->setMenuPosition($menuPosition);

        $this->title = $i . '_Title';
        $this->description = $i . '_Description';
        $this->languageCatalog = strtr(get_class($module), '\\', '_');
        $this->tags = $i . '_Tags';
    }

    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    public function setMenuPosition($menuPosition)
    {
        $this->menuPosition = is_numeric($menuPosition) ? sprintf('%05d', $menuPosition) : strval($menuPosition);
        return $this;
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
