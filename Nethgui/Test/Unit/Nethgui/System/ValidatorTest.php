<?php
namespace Nethgui\Test\Unit\Nethgui\System;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Nethgui_Core_Validator
     */
    protected $object;

    /**
     *
     * @var \Nethgui\System\PlatformInterface
     */
    private $platform;

    protected function setUp()
    {
        $this->platform = $this->getMock('\Nethgui\System\PlatformInterface', array(
            'getDateFormat',
            'exec',
            'getIdentityAdapter',
            'getMapAdapter',
            'getDatabase',
            'getTableAdapter',
            'signalEvent',
            'createValidator',
            'getDetachedProcess',
            'getDetachedProcesses',
            'runEvents',
            ));

        $this->platform
            ->expects($this->any())
            ->method('getDateFormat')
            ->will($this->returnValue('YYYY-mm-dd'));

        $this->object = new \Nethgui\System\Validator($this->platform);
    }

    public function testOrValidator()
    {
        $v1 = new \Nethgui\System\Validator($this->platform);
        $v2 = new \Nethgui\System\Validator($this->platform);
        $v1->equalTo(1);
        $v2->equalTo(2);
        $o = $this->object->orValidator($v1, $v2);
        $this->assertSame($this->object, $o);

        $this->assertTrue($this->object->evaluate(1));
        $this->assertTrue($this->object->evaluate(2));

        $this->assertFalse($this->object->evaluate(0));
        $this->assertFalse($this->object->evaluate(3));
    }

    public function testMemberOf1()
    {
        $o = $this->object->memberOf('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h');
        $this->assertSame($this->object, $o);

        $this->assertTrue($this->object->evaluate('a'));
        $this->assertTrue($this->object->evaluate('h'));
        $this->assertTrue($this->object->evaluate('d'));
        $this->assertFalse($this->object->evaluate('z'));
    }

    public function testMemberOf2()
    {
        $o = $this->object->memberOf(array('a', 'b', 'c'));
        $this->assertSame($this->object, $o);

        $this->assertTrue($this->object->evaluate('a'));
        $this->assertFalse($this->object->evaluate('z'));
    }

    public function testRegexpSuccess()
    {
        $o = $this->object->regexp('/[0-9]+/');
        $this->assertSame($this->object, $o);

        $this->assertTrue($this->object->evaluate('12345'));
    }

    public function testRegexpFail()
    {
        $o = $this->object->regexp('/[0-9]+/');
        $this->assertSame($this->object, $o);

        $this->assertFalse($this->object->evaluate('aaaaa'));
    }

    public function testNotEmpty()
    {
        $o = $this->object->notEmpty();
        $this->assertSame($this->object, $o);

        $this->assertFalse($this->object->evaluate(''));
    }

    public function testEmpty()
    {
        $o = $this->object->isEmpty();
        $this->assertSame($this->object, $o);

        $this->assertTrue($this->object->evaluate(''));
        $this->assertTrue($this->object->evaluate(FALSE));
        $this->assertTrue($this->object->evaluate(NULL));
        $this->assertTrue($this->object->evaluate(array()));
        $this->assertTrue($this->object->evaluate('0'));

        $this->assertFalse($this->object->evaluate('1'));
    }

    public function testForceResultTrue()
    {
        $o = $this->object->forceResult(TRUE)->notEmpty();
        $this->assertSame($this->object, $o);

        $this->assertTrue($this->object->evaluate(''));
    }

    public function testForceResultFalse()
    {
        $o = $this->object->notEmpty()->forceResult(FALSE);
        $this->assertSame($this->object, $o);

        $this->assertFalse($this->object->evaluate('x'));
    }

    /**
     * @todo Implement testIpV4Address().
     */
    public function testIpV4Address()
    {
        $o = $this->object->ipV4Address();
        $this->assertSame($this->object, $o);

        $this->assertTrue($this->object->evaluate('1.1.1.1'));
        $this->assertTrue($this->object->evaluate('0.0.0.0'));
        $this->assertFalse($this->object->evaluate(''));
        $this->assertFalse($this->object->evaluate('a.b.c.d'));
        $this->assertFalse($this->object->evaluate('192.168.5.002'));
        $this->assertFalse($this->object->evaluate('192.168.005.003'));
        $this->assertTrue($this->object->evaluate('0.10.20.30'));
    }

    /**
     * @todo Implement testIpV4Netmask().
     */
    public function testIpV4Netmask()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testIpV6Address().
     */
    public function testIpV6Address()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testIpV6Netmask().
     */
    public function testIpV6Netmask()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testUsernameValid()
    {
        $o = $this->object->username();
        $this->assertSame($this->object, $o);

        $this->assertTrue($this->object->evaluate('v123alid-user_name'));
    }

    public function testUsernameInvalid()
    {
        $o = $this->object->username();
        $this->assertSame($this->object, $o);

        $invalidUsernames = array(
            'invalidUserName', // no uppercase
            '0invalidusername', // start with letter
            'in*valid', // no symbols
            str_repeat('x', 256), // < 256 characters            
        );

        foreach ($invalidUsernames as $username) {
            $this->assertFalse($this->object->evaluate($username), "Invalid user name: $username");
        }
    }

    public function testCollectionValidatorNotEmptyMembers()
    {
        $v = new \Nethgui\System\Validator($this->platform);

        // check members are not empty
        $v->notEmpty();

        $cv = $this->object->collectionValidator($v);
        $this->assertSame($this->object, $cv);


        $o = new \ArrayObject(array('a', 'b', 'c'));

        $this->assertTrue($this->object->evaluate(array('a', 'b', 'c')));
        $this->assertTrue($this->object->evaluate($o));
        $this->assertTrue($this->object->evaluate(array())); // an empty collection always return TRUE!
        $this->assertTrue($this->object->evaluate($o->getIterator()));
        $this->assertFalse($this->object->evaluate(array('a', '', 'c')));
        $this->assertFalse($this->object->evaluate(new \ArrayObject(array('a', 'b', ''))));
        $this->assertFalse($this->object->evaluate(2));
        $this->assertFalse($this->object->evaluate(TRUE));
        $this->assertFalse($this->object->evaluate(1.2));
    }

    /**
     * @todo
     */
    public function testInteger()
    {
        $o = $this->object->integer();
        $this->assertSame($this->object, $o);

        $this->assertTrue($this->object->evaluate('123'));
        $this->assertFalse($this->object->evaluate('123.0'));
        $this->assertFalse($this->object->evaluate('123.1'));
        $this->assertFalse($this->object->evaluate('a'));
        $this->assertTrue($this->object->evaluate('-123'));
    }

    public function testPositive()
    {
        $o = $this->object->positive();
        $this->assertSame($this->object, $o);

        $this->assertTrue($this->object->evaluate(1.1));
        $this->assertTrue($this->object->evaluate('1.1'));

        $this->assertFalse($this->object->evaluate('0'));
        $this->assertFalse($this->object->evaluate(FALSE));
        $this->assertFalse($this->object->evaluate(-1));
    }

    public function testNegative()
    {
        $o = $this->object->negative();
        $this->assertSame($this->object, $o);

        $this->assertTrue($this->object->evaluate('-1.2'));
        $this->assertTrue($this->object->evaluate(-1));

        $this->assertFalse($this->object->evaluate(1.1));
        $this->assertFalse($this->object->evaluate('1.1'));

        $this->assertFalse($this->object->evaluate('0'));
    }

    public function testGreatThan()
    {
        $o = $this->object->greatThan('100');
        $this->assertSame($this->object, $o);

        $this->assertTrue($this->object->evaluate('101'));
        $this->assertFalse($this->object->evaluate('100'));
        $this->assertFalse($this->object->evaluate('99'));
    }

    public function testLessThan()
    {
        $o = $this->object->lessThan('100');
        $this->assertSame($this->object, $o);

        $this->assertTrue($this->object->evaluate('99'));
        $this->assertFalse($this->object->evaluate('100'));
        $this->assertFalse($this->object->evaluate('101'));
    }

    public function testEqualTo()
    {
        $o = $this->object->equalTo('100');
        $this->assertSame($this->object, $o);

        $this->assertTrue($this->object->evaluate('100'));
        $this->assertFalse($this->object->evaluate('101'));
    }

    /**
     * @exp
     */
    public function testMinLength()
    {
        $o = $this->object->minLength(3);
        $this->assertSame($this->object, $o);

        $this->assertFalse($this->object->evaluate(''));
        $this->assertFalse($this->object->evaluate('AA'));
        $this->assertTrue($this->object->evaluate('AAA'));
        $this->assertTrue($this->object->evaluate('AAAA'));

        $this->setExpectedException('InvalidArgumentException');
        $this->object->evaluate(array('a'));
    }

    public function testMaxLength()
    {
        $o = $this->object->maxLength(3);
        $this->assertSame($this->object, $o);

        $this->assertTrue($this->object->evaluate(''));
        $this->assertTrue($this->object->evaluate('AA'));
        $this->assertTrue($this->object->evaluate('AAA'));
        $this->assertFalse($this->object->evaluate('AAAA'));

        $this->setExpectedException('InvalidArgumentException');
        $this->object->evaluate(10);
    }

    public function testHostname()
    {
        $o = $this->object->hostname();
        $this->assertSame($this->object, $o);

        $this->assertTrue($this->object->evaluate('www.Nethesis.It'));
        $this->assertTrue($this->object->evaluate('A'));

        $this->assertFalse($this->object->evaluate('www.micro$oft.com'));
        $this->assertFalse($this->object->evaluate('-ww.fail.com'));
        $this->assertFalse($this->object->evaluate('www._fail.com'));
        $this->assertFalse($this->object->evaluate('www.fail.-'));
        $this->assertFalse($this->object->evaluate(''));

        //length test
        $this->assertFalse($this->object->evaluate(str_repeat('w', 65) . '.example.com'));
        $this->assertFalse($this->object->evaluate('www.' . str_repeat('.aaa', 100)));
    }

    public function testHostnameFqdn()
    {
        $o = $this->object->hostname(1);
        $this->assertSame($this->object, $o);

        $this->assertFalse($this->object->evaluate('host123'));
        $this->assertFalse($this->object->evaluate('davidep1'));
        $this->assertTrue($this->object->evaluate('host.domain'));
        $this->assertTrue($this->object->evaluate('host.domain.co.uk'));
    }

    public function testHostnameSimple()
    {
        $o = $this->object->hostname(0, 0);
        $this->assertSame($this->object, $o);

        $this->assertTrue($this->object->evaluate('host123'));
        $this->assertFalse($this->object->evaluate('host.domain'));
    }

    /**
     * @expectedException \LogicException 
     */
    public function testHostnameEx()
    {
        $this->object->hostname(15, 7);
    }

    public function testFailureInfo1()
    {
        $o = $this->object->minLength(3);
        $this->assertSame($this->object, $o);

        $this->assertFalse($this->object->evaluate('hi'));

        $failureInfo = $this->object->getFailureInfo();

        // failure info is an array
        $this->assertInternalType('array', $failureInfo);

        // one validator, one element
        $this->assertEquals(1, count($failureInfo));

        // the array is 0-indexed
        $this->assertArrayHasKey(0, $failureInfo);

        // the 0 element is an array        
        $this->assertInternalType('array', $failureInfo[0]);

        // the 0 element contains 0 and 1 indexes:
        $this->assertArrayHasKey(0, $failureInfo[0]);
        $this->assertArrayHasKey(1, $failureInfo[0]);

        // the 1 index is an array too:
        $this->assertInternalType('array', $failureInfo[0][1]);

        // the 1 index contains one element
        $this->assertEquals(1, count($failureInfo[0][1]));
    }

    public function testDateSmallEndian()
    {
        $o = $this->object->date('dd/mm/YYYY');
        $this->assertSame($this->object, $o);

        $this->assertTrue($this->object->evaluate('31/12/1999'));
        $this->assertTrue($this->object->evaluate('1/1/1999'));

        $this->assertFalse($this->object->evaluate(''));
        $this->assertFalse($this->object->evaluate('12-31-1999'));
        $this->assertFalse($this->object->evaluate('1999-31-12'));
        $this->assertFalse($this->object->evaluate('0/0/0'));
        $this->assertFalse($this->object->evaluate('29-02-1999'));
        $this->assertFalse($this->object->evaluate('29/02/1999'));
    }

    public function testDateMiddleEndian()
    {
        $o = $this->object->date('mm-dd-YYYY');
        $this->assertSame($this->object, $o);

        $this->assertTrue($this->object->evaluate('12-31-1999'));
        $this->assertTrue($this->object->evaluate('1-1-1999'));

        $this->assertFalse($this->object->evaluate(''));
        $this->assertFalse($this->object->evaluate('31/12/1999'));
        $this->assertFalse($this->object->evaluate('1999-31-12'));
        $this->assertFalse($this->object->evaluate('0-0-0'));
        $this->assertFalse($this->object->evaluate('02-29-1999'));
        $this->assertFalse($this->object->evaluate('02/29/1999'));
    }

    public function testDateBigEndian()
    {
        $o = $this->object->date('YYYY-mm-dd');
        $this->assertSame($this->object, $o);

        $this->assertTrue($this->object->evaluate('1999-12-31'));
        $this->assertFalse($this->object->evaluate('1999-31-12'));
    }

    public function testDateDefault()
    {
        $o = $this->object->date();
        $this->assertSame($this->object, $o);

        $this->assertTrue($this->object->evaluate('1999-12-31'));
    }

    public function testDateUnknownFormat()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->object->date('mm.dd.yyyy');
        $this->object->evaluate('1999-12-31');
    }

    public function testTime()
    {
        $o = $this->object->time();
        $this->assertSame($this->object, $o);

        $this->assertTrue($this->object->evaluate('00:00'));
        $this->assertTrue($this->object->evaluate('23:59'));

        $this->assertFalse($this->object->evaluate('24:00'));
        $this->assertFalse($this->object->evaluate('1:0'));
    }

    public function testPlatform1()
    {
        $o = $this->object->platform('test', 'a1', 'a2');
        $this->assertSame($this->object, $o);

        $processMockSuccess = $this->getMock('\Nethgui\System\ProcessInterface', array(
            'getOutput',
            'getOutputArray',
            'readOutput',
            'getExitCode',
            'addArgument',
            'exec',
            'kill',
            'setIdentifier',
            'getIdentifier',
            'readExecutionState',
            'getTimes',
            'isDisposed',
            'dispose')
        );

        $processMockSuccess->expects($this->any())
            ->method('getExitCode')
            ->will($this->returnValue(0));

        $processMockSuccess->expects($this->any())
            ->method('getOutput')
            ->will($this->returnValue(''));

        $this->platform
            ->expects($this->once())
            ->method('exec')
            ->with('/usr/bin/sudo /sbin/e-smith/validate ${@}', array('test', 'a1', 'a2', 'value1'))
            ->will($this->returnValue($processMockSuccess));

        $this->assertTrue($this->object->evaluate('value1'));
    }

    public function testPlatform2()
    {
        $o = $this->object->platform('test', 'a1', 'a2');
        $this->assertSame($this->object, $o);

        $processMockFail = $this->getMockBuilder('\Nethgui\System\ProcessInterface')
            //->setMethods(array('getExitStatus', 'getOutput'))
            ->getMock();

        $processMockFail->expects($this->any())
            ->method('getExitStatus')
            ->will($this->returnValue(1));

        $processMockFail->expects($this->any())
            ->method('getOutput')
            ->will($this->returnValue("Invalid value\nExiting..."));

        $this->platform
            ->expects($this->once())
            ->method('exec')
            ->with('/usr/bin/sudo /sbin/e-smith/validate ${@}', array('test', 'a1', 'a2', 'value2'))
            ->will($this->returnValue($processMockFail));

        $this->assertFalse($this->object->evaluate('value2'));
    }

    public function testEmailValid()
    {
        $o = $this->object->email();
        $this->assertSame($this->object, $o);
        $eval = $this->object->evaluate('my_valid.e-m4il@domain.tld');
        $failureInfo = $this->object->getFailureInfo();
        $this->assertTrue($eval, 'Validation failed. Reason: ' . ($eval === FALSE ? $failureInfo[0][0] : ''));
    }

    public function testEmailInvalid()
    {
        $o = $this->object->email();
        $this->assertSame($this->object, $o);

        $invalidEmails = array(
            // no domain            
            array('invalidUserName', 'valid_email,missing-domainpart'),
            // no localpart
            array('@domain.tld', 'valid_email,missing-localpart'),
            // start with letter 
            array('.invalidusername@domain.tld', 'valid_email,malformed-localpart'),
            // no symbols           
            array('in(valid)@domain.tld', 'valid_email,malformed-localpart'),
            // no double-dots
            array('in..valid@domain.tld', 'valid_email,malformed-localpart'),
            // no dot at end
            array('invalid.@domain.tld', 'valid_email,malformed-localpart'),
            // localpart <= 64 chars 
            array(str_repeat('x', 65) . '@domain.tld', 'valid_email,malformed-localpart'),
            // localpart <= 254 chars 
            array(str_repeat('x', 244) . '@domain.tld', 'valid_email,too-long'),
            // invalid domain name 
            array('my.email@.domain', 'valid_email,malformed-domainpart'),
        );

        foreach ($invalidEmails as $test) {
            $this->assertFalse($this->object->evaluate($test[0]), "Invalid email address: " . $test[0]);
            $this->assertEquals(array(array($test[1], array())), $this->object->getFailureInfo(), 'Testing ' . $test[0]);
        }
    }

    public function testCidrBlock()
    {
        $o = $this->object->cidrBlock();
        $this->assertSame($this->object, $o);

        $this->assertTrue($o->evaluate('12.13.14.15/24'));
        $this->assertFalse($o->evaluate('12.13.14.15'));
        $this->assertFalse($o->evaluate('12.13.14.15/'));
        $this->assertFalse($o->evaluate('12.13.14.15/2aaa'));
        $this->assertFalse($o->evaluate('12.13.14.15/q'));
        $this->assertFalse($o->evaluate('1/1'));
        $this->assertFalse($o->evaluate('12.13.14.300/12'));
    }

}
