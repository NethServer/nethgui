<?php
/**
 * @package Test
 * @subpackage Tool
 */

/**
 * @package Test
 * @subpackage Tool
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class Test\Tool\MockObject implements PHPUnit_Framework_MockObject_Stub
{

    /**
     *
     * @var Test\Tool\MockState
     */
    private $state = NULL;

    public function __construct(Test\Tool\MockState $state)
    {
        $this->state = $state;
    }

    /**
     *
     * @return Test\Tool\MockState
     */
    public function getState()
    {
        return $this->state;
    }

    public function invoke(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        $methodName = $invocation->methodName;
        $parameters = $invocation->parameters;
        $returnValue = NULL;
        $what = array($methodName, $parameters);
        $this->state = $this->state->exec($what, $returnValue);
        return $returnValue;
    }

    public function toString()
    {
        return PHPUnit_Util_Type::toString($this->state);
    }

}