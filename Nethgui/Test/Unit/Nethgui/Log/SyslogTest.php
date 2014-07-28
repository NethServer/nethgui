<?php
namespace Nethgui\Test\Unit\Nethgui\Log;

/**
 * @covers \Nethgui\Log\Syslog
 * @covers \Nethgui\Log\AbstractLog
 */
class SyslogTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Nethgui\Log\Syslog
     */
    protected $object;

    /**
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $php;

    protected function setUp()
    {
        $this->php = $this->getMock('Nethgui\Utility\PhpWrapper', array('syslog'));

        $this->object = new \Nethgui\Log\Syslog();
        $this->object->setPhpWrapper($this->php);
    }

    public function testMessage()
    {
        $this->php->expects($this->at(0))
            ->method('syslog')
            ->with(LOG_NOTICE, $this->logicalAnd($this->stringContains('NOTICE'), $this->stringContains('HelloWorld')))
            ->will($this->returnValue($this->object));

        $this->php->expects($this->at(1))
            ->method('syslog')
            ->with(LOG_WARNING, $this->logicalAnd($this->stringContains('WARNING'), $this->stringContains('WarningWorld')))
            ->will($this->returnValue($this->object));

        $this->php->expects($this->at(2))
            ->method('syslog')
            ->with(LOG_ERR, $this->logicalAnd($this->stringContains('ERROR'), $this->stringContains('ErrorWorld')))
            ->will($this->returnValue($this->object));

        $this->php->expects($this->at(3))
            ->method('syslog')
            ->with(LOG_ERR, $this->logicalAnd($this->stringContains('EXCEPTION'), $this->stringContains('Failure1')))
            ->will($this->returnValue($this->object));

       $this->php->expects($this->at(4))
            ->method('syslog')
            ->with(LOG_ERR, $this->logicalAnd($this->stringContains('EXCEPTION'), $this->stringContains('Failure2')))
            ->will($this->returnValue($this->object));

        $r = $this->object->notice('HelloWorld');
        $this->assertSame($this->object, $r);
        $r = $this->object->warning('WarningWorld');
        $this->assertSame($this->object, $r);
        $r = $this->object->error('ErrorWorld');
        $this->assertSame($this->object, $r);
        $r = $this->object->exception(new \Exception('Failure1', 1234), FALSE);
        $this->assertSame($this->object, $r);
        $r = $this->object->exception(new \Exception('Failure2', 1234), TRUE);
        $this->assertSame($this->object, $r);

    }

}
