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
class Test_Tool_MockObject implements PHPUnit_Framework_MockObject_Stub
{

    /**
     *
     * @var Test_Tool_MockState
     */
    private $state = NULL;

    public function __construct(Test_Tool_MockState $state)
    {
        $this->state = $state;
    }

    /**
     *
     * @return Test_Tool_MockState
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