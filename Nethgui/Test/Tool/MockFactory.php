<?php
namespace Nethgui\Test\Tool;

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
 * Create mocked instances of framework objects
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class MockFactory
{

    /**
     *
     * @param string $username
     * @return \Nethgui\Authorization\UserInterface
     */
    public static function getAuthenticationSubject(\PHPUnit_Framework_TestCase $testcase, $username = FALSE)
    {
        $subject = $testcase->getMock('Nethgui\Authorization\User', array('authenticate', 'isAuthenticated', 'getCredential', 'hasCredential', 'getLanguageCode'));

        $subject->expects($testcase->any())
            ->method('isAuthenticated')
            ->will($testcase->returnValue(is_string($username)));

        $subject->expects($testcase->any())
            ->method('getCredential')
            ->with('username')
            ->will($testcase->returnValue(is_string($username) ? $username : NULL));

        $subject->expects($testcase->any())
            ->method('hasCredential')
            ->with('username')
            ->will($testcase->returnValue(is_string($username)));

        $subject->hasCredential('username');
        $subject->getCredential('username');
        $subject->isAuthenticated();

        return $subject;
    }

}