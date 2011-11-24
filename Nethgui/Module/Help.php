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
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class Help extends \Nethgui\Core\Module\Controller
{

    /**
     *
     * @var \Nethgui\Core\ModuleSetInterface
     */
    private $moduleSet;

    public function __construct(\Nethgui\Core\ModuleSetInterface $moduleSet)
    {
        parent::__construct(NULL);
        $this->moduleSet = $moduleSet;
    }

    public function initialize()
    {
        parent::initialize();
        $this->loadChildren(array('Show', 'Template', 'Read'));

        // Propagate moduleSet to children
        foreach ($this->getChildren() as $child) {
            $child->moduleSet = $this->moduleSet;
        }
    }

    public function prepareView(\Nethgui\Core\ViewInterface $view, $mode)
    {
        if (is_null($this->currentAction)) {
            $view->setTemplate('Nethgui\Template\Help');
        } else {
            parent::prepareView($view, $mode);
        }
    }

}
