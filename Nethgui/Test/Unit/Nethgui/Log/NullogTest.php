<?php
namespace Nethgui\Test\Unit\Nethgui\Log;

/**
 * @covers \Nethgui\Log\Nullog
 */
class NullogTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Nethgui\Log\Nullog
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new \Nethgui\Log\Nullog;
    }

    public function testNullog()
    {
        $this->assertSame($this->object, $this->object->notice('Nothing to test?'));
        $this->assertSame($this->object, $this->object->warning('Nothing to test?'));
        $this->assertSame($this->object, $this->object->error('Nothing to test?'));
    }

}

