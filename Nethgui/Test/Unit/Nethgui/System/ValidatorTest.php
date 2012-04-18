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
        $this->object->orValidator($v1, $v2);

        $this->assertTrue($this->object->evaluate(1));
        $this->assertTrue($this->object->evaluate(2));

        $this->assertFalse($this->object->evaluate(0));
        $this->assertFalse($this->object->evaluate(3));
    }

    public function testMemberOf1()
    {
        $this->object->memberOf('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h');
        $this->assertTrue($this->object->evaluate('a'));
        $this->assertTrue($this->object->evaluate('h'));
        $this->assertTrue($this->object->evaluate('d'));
        $this->assertFalse($this->object->evaluate('z'));
    }

    public function testMemberOf2()
    {
        $this->object->memberOf(array('a', 'b', 'c'));
        $this->assertTrue($this->object->evaluate('a'));
        $this->assertFalse($this->object->evaluate('z'));
    }

    public function testRegexpSuccess()
    {
        $this->object->regexp('/[0-9]+/');
        $this->assertTrue($this->object->evaluate('12345'));
    }

    public function testRegexpFail()
    {
        $this->object->regexp('/[0-9]+/');
        $this->assertFalse($this->object->evaluate('aaaaa'));
    }

    public function testNotEmpty()
    {
        $this->object->notEmpty();
        $this->assertFalse($this->object->evaluate(''));
    }

    public function testEmpty()
    {
        $this->object->isEmpty();
        $this->assertTrue($this->object->evaluate(''));
        $this->assertTrue($this->object->evaluate(FALSE));
        $this->assertTrue($this->object->evaluate(NULL));
        $this->assertTrue($this->object->evaluate(array()));
        $this->assertTrue($this->object->evaluate('0'));

        $this->assertFalse($this->object->evaluate('1'));
    }

    public function testForceResultTrue()
    {
        $this->object->forceResult(TRUE)->notEmpty();
        $this->assertTrue($this->object->evaluate(''));
    }

    public function testForceResultFalse()
    {
        $this->object->notEmpty()->forceResult(FALSE);
        $this->assertFalse($this->object->evaluate('x'));
    }

    /**
     * @todo Implement testIpV4Address().
     */
    public function testIpV4Address()
    {
        $this->object->ipV4Address();

        $this->assertTrue($this->object->evaluate('1.1.1.1'));
        $this->assertFalse($this->object->evaluate('0.1.1.1'));
        $this->assertFalse($this->object->evaluate(''));
        $this->assertFalse($this->object->evaluate('a.b.c.d'));
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
        $this->object->username();
        $this->assertTrue($this->object->evaluate('v123alid-user_name'));
    }

    public function testUsernameInvalid()
    {
        $this->object->username();

        $invalidUsernames = array(
            'invalidUserName', // no uppercase
            '0invalidusername', // start with letter
            'in.valid', // no symbols           
            str_repeat('x', 32), // < 32 characters            
        );

        foreach ($invalidUsernames as $username) {
            $this->assertFalse($this->object->evaluate($username));
        }
    }

    public function testCollectionValidatorNotEmptyMembers()
    {
        $v = new \Nethgui\System\Validator($this->platform);

        // check members are not empty
        $v->notEmpty();

        $this->object->collectionValidator($v);

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
        $this->object->integer();

        $this->assertTrue($this->object->evaluate('123'));
        $this->assertTrue($this->object->evaluate('123.0'));
        $this->assertFalse($this->object->evaluate('123.1'));
        $this->assertFalse($this->object->evaluate('a'));
        $this->assertTrue($this->object->evaluate('-123'));
    }

    public function testPositive()
    {
        $this->object->positive();

        $this->assertTrue($this->object->evaluate(1.1));
        $this->assertTrue($this->object->evaluate('1.1'));

        $this->assertFalse($this->object->evaluate('0'));
        $this->assertFalse($this->object->evaluate(FALSE));
        $this->assertFalse($this->object->evaluate(-1));
    }

    public function testNegative()
    {
        $this->object->negative();
        $this->assertTrue($this->object->evaluate('-1.2'));
        $this->assertTrue($this->object->evaluate(-1));

        $this->assertFalse($this->object->evaluate(1.1));
        $this->assertFalse($this->object->evaluate('1.1'));

        $this->assertFalse($this->object->evaluate('0'));
    }

    public function testGreatThan()
    {
        $this->object->greatThan('100');

        $this->assertTrue($this->object->evaluate('101'));
        $this->assertFalse($this->object->evaluate('100'));
        $this->assertFalse($this->object->evaluate('99'));
    }

    public function testLessThan()
    {
        $this->object->lessThan('100');

        $this->assertTrue($this->object->evaluate('99'));
        $this->assertFalse($this->object->evaluate('100'));
        $this->assertFalse($this->object->evaluate('101'));
    }

    public function testEqualTo()
    {
        $this->object->equalTo('100');

        $this->assertTrue($this->object->evaluate('100'));
        $this->assertFalse($this->object->evaluate('101'));
    }

    /**
     * @exp
     */
    public function testMinLength()
    {
        $this->object->minLength(3);

        $this->assertFalse($this->object->evaluate(''));
        $this->assertFalse($this->object->evaluate('AA'));
        $this->assertTrue($this->object->evaluate('AAA'));
        $this->assertTrue($this->object->evaluate('AAAA'));

        $this->setExpectedException('InvalidArgumentException');
        $this->object->evaluate(array('a'));
    }

    public function testMaxLength()
    {
        $this->object->maxLength(3);

        $this->assertTrue($this->object->evaluate(''));
        $this->assertTrue($this->object->evaluate('AA'));
        $this->assertTrue($this->object->evaluate('AAA'));
        $this->assertFalse($this->object->evaluate('AAAA'));

        $this->setExpectedException('InvalidArgumentException');
        $this->object->evaluate(10);
    }

    public function testHostname()
    {
        $this->object->hostname();

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
        $this->object->hostname(1);
        $this->assertFalse($this->object->evaluate('host123'));
        $this->assertFalse($this->object->evaluate('davidep1'));
        $this->assertTrue($this->object->evaluate('host.domain'));        
        $this->assertTrue($this->object->evaluate('host.domain.co.uk'));     
    }

    public function testHostnameSimple()
    {
        $this->object->hostname(0, 0);
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
    
    public function testFailureInfo1() {
        $this->object->minLength(3);
        
        $this->assertFalse($this->object->evaluate('hi'));
        
        $failureInfo = $this->object->getFailureInfo();
        
        // failure info is an array
        $this->assertType('array', $failureInfo);        
        
        // one validator, one element
        $this->assertEquals(1, count($failureInfo));
        
        // the array is 0-indexed
        $this->assertArrayHasKey(0, $failureInfo);
        
        // the 0 element is an array        
        $this->assertType('array', $failureInfo[0]);
        
               
        var_export($failureInfo);
       
        // the 0 element contains 0 and 1 indexes:
        $this->assertArrayHasKey(0, $failureInfo[0]);
        $this->assertArrayHasKey(1, $failureInfo[0]);
        
        // the 1 index is an array too:
        $this->assertType('array', $failureInfo[0][1]);
        
        // the 1 index contains one element
        $this->assertEquals(1, count($failureInfo[0][1]));
    }

    public function testDateSmallEndian()
    {
        $this->object->date('dd/mm/YYYY');

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
        $this->object->date('mm-dd-YYYY');

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
        $this->object->date('YYYY-mm-dd');

        $this->assertTrue($this->object->evaluate('1999-12-31'));
        $this->assertFalse($this->object->evaluate('1999-31-12'));
    }

    public function testDateDefault()
    {
        $this->object->date();

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
        $this->object->time();

        $this->assertTrue($this->object->evaluate('00:00'));
        $this->assertTrue($this->object->evaluate('23:59'));

        $this->assertFalse($this->object->evaluate('24:00'));
        $this->assertFalse($this->object->evaluate('1:0'));
    }

    public function testPlatform1()
    {
        $this->object->platform('test', 'a1', 'a2');

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
        $this->object->platform('test', 'a1', 'a2');

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

}
