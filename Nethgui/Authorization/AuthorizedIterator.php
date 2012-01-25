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
 * @internal
 */
class AuthorizedIterator extends \FilterIterator
{

    /**
     *
     * @var \Nethgui\Authorization\PolicyDecisionPointInterface
     */
    private $pdp;

    /**
     *
     * @var \Nethgui\Authorization\UserInterface
     */
    private $subject;

    /**
     *
     * @param \Iterator $iterator
     * @param \Nethgui\Authorization\PolicyDecisionPointInterface $pdp
     */
    public function __construct(\Iterator $iterator, \Nethgui\Authorization\PolicyDecisionPointInterface $pdp, \Nethgui\Authorization\UserInterface $subject)
    {
        parent::__construct($iterator);
        $this->pdp = $pdp;
        $this->subject = $subject;
    }

    public function accept()
    {
        return $this->pdp
                ->authorize($this->subject, $this->current(), \Nethgui\Authorization\PolicyDecisionPointInterface::QUERY)
                ->isGranted();
    }

}