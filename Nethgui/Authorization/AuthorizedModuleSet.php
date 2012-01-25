<?php
namespace Nethgui\Authorization;

/*
 * Copyright (C) 2012 Nethesis S.r.l.
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
 * Enforce authorization policy on the inner module set
 *
 * @see http://en.wikipedia.org/wiki/Decorator_pattern
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class AuthorizedModuleSet implements \Nethgui\Module\ModuleSetInterface, \Nethgui\Authorization\PolicyEnforcementPointInterface
{

    /**
     *
     * @param \Nethgui\Module\ModuleSetInterface $moduleSet
     */
    private $moduleSet;

    /**
     *
     * @var Nethgui\Authorization\UserInterface
     */
    private $user;

    /**
     *
     * @var \Nethgui\Authorization\PolicyDecisionPointInterface
     */
    private $pdp;

    /**
     *
     * @var array
     */
    private $checkedModules;

    public function __construct(\Nethgui\Module\ModuleSetInterface $moduleSet, \Nethgui\Authorization\UserInterface $user)
    {
        $this->moduleSet = $moduleSet;
        $this->checkedModules = array();
        $this->user = $user;
    }

    public function getIterator()
    {
        return new \Nethgui\Authorization\AuthorizedIterator($this->moduleSet->getIterator(), $this->pdp, $this->user);
    }

    public function getModule($moduleIdentifier)
    {
        $module = $this->moduleSet->getModule($moduleIdentifier);

        if ( ! isset($this->checkedModules[$moduleIdentifier])) {
            $this->setup($module);
            $this->checkedModules[$moduleIdentifier] = TRUE;
        }

        return $module;
    }

    private function setup(\Nethgui\Module\ModuleInterface $module)
    {
        $access = $this->pdp->authorize($this->user, get_class($module), \Nethgui\Authorization\PolicyDecisionPointInterface::INSTANTIATE);
        if ($access->isDenied()) {
            throw $access->asException(1327492764);
        }

        if ($module instanceof Nethgui\Authorization\PolicyEnforcementPointInterface) {
            $module->setPolicyDecisionPoint($this->pdp);
        }
    }

    public function setPolicyDecisionPoint(\Nethgui\Authorization\PolicyDecisionPointInterface $pdp)
    {
        $this->pdp = $pdp;
        return $this;
    }

}