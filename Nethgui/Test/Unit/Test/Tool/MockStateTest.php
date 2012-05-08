<?php
namespace Test\Unit\Test\Tool;
class MockStateTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Test\Tool\MockState
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new \Test\Tool\MockState;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }

    public function testReturnValues()
    {
        $o1 = $this->object->set('getV1', 1);
        $this->assertSame($o1, $this->object);

        $o2 = $this->object->set('getV2', 2);
        $this->assertSame($o2, $this->object);

        $t = $this->object->transition('setV3');
        $this->assertNotSame($t, $this->object);
    }

    public function testSetValuesThenExec()
    {
        $this->object->set('getV1', 1);
        $this->object->set('getV2', 2);

        $v1 = NULL;
        $v2 = NULL;

        $this->object->exec('getV1', $v1);
        $this->object->exec('getV2', $v2);

        $this->assertEquals(1, $v1);
        $this->assertEquals(2, $v2);
    }

    public function testTransition()
    {
        $o0 = $this->object;

        $o0->set('a', 1);

        $o1 = $o0->transition('a++');

        $o1->set('a', 2);

        $aOut = NULL;
        $o0->exec('a', $aOut);
        $this->assertEquals(1, $aOut);

        $o1->exec('a', $aOut);
        $this->assertEquals(2, $aOut);

        $fOut = '';
        $this->assertSame($o1, $o0->exec('a++', $fOut));
        $this->assertNull($fOut);
    }

    public function testFull()
    {

        $o = $this->object;
        $o->set('k', 3);

        for ($i = 0; $i < 3; $i ++ ) {
            $o = $o->set('i', $i)->transition('i++');
        }

        $o = $this->object;
        for ($i = 0; $i < 3; $i ++ ) {
            $kOut = NULL;
            $o = $o->exec('k', $kOut);
            $this->assertEquals(3, $kOut);

            $iOut = NULL;
            $o = $o->exec('i', $iOut);
            $this->assertEquals($i, $iOut);

            $o = $o->exec('i++', $iOut);
            $this->assertNull($iOut);
        }
    }

    public function testCall()
    {
        $this->assertEquals(array('m', array(1, 2, 3)), $this->object->call('m', 1, 2, 3));
        $this->assertEquals(array('n', array()), $this->object->call('n'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCallInvalid()
    {
        $v = $this->object->call();
    }

    /**
     * @expectedException PHPUnit_Framework_ExpectationFailedException
     */
    public function testExecFail()
    {
        $output = NULL;
        $this->object->exec('FAIL', $output);
        $this->assertEquals(NULL, $output);
    }

    public function testSetFinal()
    {
        $this->object->setFinal();
        $this->assertTrue($this->object->isFinal());
    }

}

