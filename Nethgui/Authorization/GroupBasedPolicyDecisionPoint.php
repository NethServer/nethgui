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
 * TODO: add component description here
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class GroupBasedPolicyDecisionPoint implements PolicyDecisionPointInterface, \Nethgui\Log\LogConsumerInterface
{

    /**
     *
     * @var \Nethgui\Log\LogInterface
     */
    private $log;

    public function authorizeUser(UserInterface $subject, $resource, $action, &$message)
    {
        $message = '';
        $resourceId = is_object($resource) ? get_class($resource) : strval($resource);
        $username = $subject->hasCredential('username') && $subject->isAuthenticated() ? $subject->getCredential('username') : 'NOBODY';
        
        if (preg_match('/^Nethgui\\\Module\\\(Resource|Login|Menu|Notification|Logout)\\\?.*$/', $resourceId) > 0
            || preg_match('/^Nethgui\\\System\\\ConfigurationDatabase\\\(configuration)$/', $resourceId) > 0
            || $action === self::INSTANTIATE) {
            $granted = TRUE;
        } elseif ($subject->isAuthenticated()) {
            $granted = TRUE;
        } else {            
            $granted = FALSE;
        }

        $message = sprintf('%s `%s` access on `%s` to `%s`.', $granted ? 'Granted' : 'Denied', $action, $resourceId, $username);

        $this->getLog()->debug(sprintf('%s: %s', __CLASS__, $message));

        return $granted ? 0 : 1;
    }

    public function authorize(UserInterface $subject, $resource, $action)
    {
        $pdp = $this;
        return new LazyAccessControlResponse(function(&$message) use ($pdp, $subject, $resource, $action) {
                    return $pdp->authorizeUser($subject, $resource, $action, $message);
                });
    }

    public function getLog()
    {
        if ( ! isset($this->log)) {
            $this->log = new \Nethgui\Log\Nullog();
        }

        return $this->log;
    }

    public function setLog(\Nethgui\Log\LogInterface $log)
    {
        $this->log = $log;
        return $this;
    }

}