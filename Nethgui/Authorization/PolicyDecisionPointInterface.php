<?php
namespace Nethgui\Authorization;

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
 * PolicyDecisionPointInterface (PDP)
 *
 * A PDP implementation is responsible for authorizing access requests coming
 * from PEPs (PolicyEnforcementPointInterfaces).
 *
 * @api
 * @see PolicyEnforcementPointInterface
 */
interface PolicyDecisionPointInterface
{
    /**
     * Authorize the $subject to perform $action on $resource
     *
     * Each parameter may implement AuthorizationAttributesProviderInterface
     *
     * @api
     * @param string|object The Subject
     * @param string|object The resource requested
     * @param string|object The action to be performed
     * @return AccessControlResponseInterface
     */
    public function authorize($subject, $resource, $action);

}

