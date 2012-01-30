<?php
namespace Nethgui\Test\Unit\Nethgui\Authorization;

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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NethServer. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * PolicyRule Unit test case
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @covers \Nethgui\Authorization\PolicyRule
 * @covers \Nethgui\Authorization\PolicyExpressionParser
 * @covers \Nethgui\Authorization\AbstractPolicyRuleMatcher
 * @covers \Nethgui\Authorization\AnyOfPolicyRuleMatcher
 * @covers \Nethgui\Authorization\AllOfPolicyRuleMatcher
 * @covers \Nethgui\Authorization\AttributePolicyRuleMatcher
 * @covers \Nethgui\Authorization\NegationPolicyRuleMatcher
 * @covers \Nethgui\Authorization\StarPolicyRuleMatcher
 * @covers \Nethgui\Authorization\BinaryOperatorRuleMatcher
 */
class PolicyRuleTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var PolicyRule
     */
    protected $object;
    private $rules = array();

    protected function setUp()
    {
        foreach (array(
        0 => '{ "Id": 1$INDEX, "Effect": "DENY", "Subject": "*", "Action": "*", "Resource": "*", "Description": "Desc$INDEX", "Final": true }',
        1 => '{ "Id": 1$INDEX, "Effect": "ALLOW", "Subject": "admin", "Action": "*", "Resource": "*", "Description": "Desc$INDEX" }',
        2 => '{ "Id": 1$INDEX, "Effect": "ALLOW", "Subject": "*", "Action": "QUERY || !MUTATE", "Resource": "Resource1\\\\* && (!Res4 || Res5)", "Description": "Desc$INDEX" }',
        3 => '{ "Id": 1$INDEX, "Effect": "ALLOW", "Subject": "*", "Action": ["QUERY", "!MUTATE"], "Resource": "Resource2\\\\* && !Res4", "Description": "Desc$INDEX" }',
        4 => '{ "Id": 1$INDEX, "Effect": "DENY", "Subject": "dude || (a && !(!(b))) || .isAuthenticated", "Resource": "*", "Description": "Desc$INDEX" }',
        5 => '{ "Id": 1$INDEX, "Effect": "DENY", "Subject": false, "Resource": "*", "Description": "Desc$INDEX" }',
        6 => '{ "Id": 1$INDEX, "Effect": "DENY", "Subject": "*", "Action": "*", "Resource": "a && ", "Description": "Desc$INDEX" }', // ERROR
        7 => '{ "Id": 1$INDEX, "Effect": "DENY", "Subject": "*", "Action": "*", "Resource": "a && ( b", "Description": "Desc$INDEX" }', // ERROR
        8 => '{ "Id": 1$INDEX, "Effect": "ALLOW", "Subject": "admin || .isAuthenticated", "Action": "*", "Resource": "*", "Description": "Desc$INDEX" }',
        9 => '{ "Id": 1$INDEX, "Effect": "ALLOW", "Subject": "!.isAuthenticated || .notExist", "Action": "*", "Resource": "*", "Description": "Desc$INDEX" }',
        10 => '{ "Id": 1$INDEX, "Effect": "ALLOW", "Subject": "*", "Action": [], "Resource": "*", "Description": "Desc$INDEX" }',
        11 => '{ "Id": 1$INDEX, "Effect": "ALLOW", "Subject": ".isAuthenticated", "Action": "*", "Resource": "*", "Description": "Desc$INDEX", "Final": true }',
        ) as $index => $rule) {
            $this->rules[$index] = strtr($rule, array('$INDEX' => $index));
        }
    }

    /**
     *
     * @param integer $index
     * @return \Nethgui\Authorization\PolicyRule
     */
    private function getRule($index)
    {
        return \Nethgui\Authorization\PolicyRule::createFromObject(json_decode($this->rules[$index]));
    }

    public function testIsFinal()
    {
        $this->assertTrue($this->getRule(0)->isFinal());
        $this->assertFalse($this->getRule(1)->isFinal());
    }

    public function testGetDescription()
    {
        $this->assertRegExp('/^Desc0/', $this->getRule(0)->getDescription());
        $this->assertRegExp('/^Desc1/', $this->getRule(1)->getDescription());
        $this->assertRegExp('/^Desc2/', $this->getRule(2)->getDescription());
    }

    public function testGetIdentifier()
    {
        $this->assertEquals(10, $this->getRule(0)->getIdentifier());
        $this->assertEquals(11, $this->getRule(1)->getIdentifier());
        $this->assertEquals(12, $this->getRule(2)->getIdentifier());
    }

    public function testIsAllow()
    {
        $this->assertTrue($this->getRule(1)->isAllow());
        $this->assertFalse($this->getRule(0)->isAllow());
    }

    private function getSubject($username = FALSE)
    {
        return \Nethgui\Test\Tool\MockFactory::getAuthenticationSubject($this, $username);
    }

    public function testIsApplicableTo1()
    {
        $this->assertTrue($this->getRule(0)->isApplicableTo($this->getSubject('dude'), 'ResourceX', 'PLAY'));
        $this->assertTrue($this->getRule(0)->isApplicableTo($this->getSubject(FALSE), 'ResourceX', 'PLAY'));
        // check missing matcher
        $this->assertTrue($this->getRule(4)->isApplicableTo($this->getSubject('dude'), new ResourceX(), 'PLAY'));
        // check missing matcher
        $this->assertFalse($this->getRule(1)->isApplicableTo($this->getSubject('dude'), new ResourceY(), 'PLAY'));
        // check missing matcher
        $this->assertTrue($this->getRule(8)->isApplicableTo($this->getSubject('dude'), new ResourceY(), 'PLAY'));
        $this->assertFalse($this->getRule(9)->isApplicableTo($this->getSubject('dude'), new ResourceY(), 'PLAY'));
        $this->assertFalse($this->getRule(10)->isApplicableTo($this->getSubject('dude'), get_class(new ResourceY()), 'PLAY'));

        $this->assertTrue($this->getRule(11)->isApplicableTo($this->getSubject('dude'), 'R', 'A'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testIsApplicableTo2()
    {
        $this->getRule(5);
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testIsApplicableTo3()
    {
        // parse error
        $this->getRule(6);
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testIsApplicableTo4()
    {
        // parse error
        $this->getRule(7);
    }

    public function testCompare1()
    {
        $r0 = $this->getRule(0);
        $r1 = $this->getRule(1);
        $r2 = $this->getRule(2);
        $r3 = $this->getRule(3);

        $this->assertLessThan(0, $r0->compare($r1));
        $this->assertGreaterThan(0, $r1->compare($r0));

        $this->assertGreaterThan(0, $r1->compare($r2));
        $this->assertLessThan(0, $r2->compare($r1));

        $this->assertEquals(0, $r2->compare($r3));
        $this->assertEquals(0, $r3->compare($r2));
    }

    public function testCompare2()
    {
        $r1 = $this->getRule(1);
        $r8 = $this->getRule(8);
        $this->assertLessThan(0, $r8->compare($r1));
        $this->assertGreaterThan(0, $r1->compare($r8));
    }

    public function testCompare3()
    {
        $r1 = $this->getRule(1);
        $r9 = $this->getRule(9);
        $this->assertLessThan(0, $r9->compare($r1));
        $this->assertGreaterThan(0, $r1->compare($r9));
    }

    public function objectProvider()
    {
        $tests = array();

        foreach ($this->rules as $index => $test) {
            $tests[] = array(json_decode($test));
        }

        return $tests;
    }

}

class ResourceX
{

    public function __toString()
    {
        return "Resource X";
    }

}

class ResourceY
{

}