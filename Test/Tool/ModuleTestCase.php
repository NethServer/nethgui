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
abstract class Test_Tool_ModuleTestCase extends PHPUnit_Framework_TestCase
{

    private $dbObjectCheckList = array();

    protected function runModuleTest(Nethgui_Core_ModuleInterface $module, Test_Tool_ModuleTestEnvironment $env)
    {
        $platform = $this->createHostConfigurationMock($env);
        $module->setPlatform($platform);
        $module->initialize();

        if ($module instanceof Nethgui_Core_RequestHandlerInterface) {
            $request = $this->createRequestMock($env);
            $validationReport = $this->createValidationReportMock($env);
            $module->bind($request);
            $module->validate($validationReport);
            $module->process();
        }

        $view = $this->createViewMock($module, $env);
        $module->prepareView($view, $env->getViewMode());

        $platform->signalFinalEvents();

        foreach ($env->getView() as $key => $value) {
            $this->assertEquals($value, $view[$key], "View parameter `{$key}`.");
        }

        $this->fullViewOutput = $view->getClientEvents();

        foreach ($this->dbObjectCheckList as $dbStubInfo) {
            $this->assertTrue($dbStubInfo[1]->getState()->isFinal(), sprintf('Database `%s` is not in final state! %s', $dbStubInfo[0], $dbStubInfo[1]->getState()));
        }
    }

    protected function createHostConfigurationMock(Test_Tool_ModuleTestEnvironment $env)
    {
        $configurationMock = $this->getMockBuilder('Nethgui_System_NethPlatform')
            ->disableOriginalConstructor()
            ->setMethods(array('getDatabase', 'signalEvent', 'exec'))
            ->getMock()
        ;

        // Value is TRUE if the method modifies the database state.
        $databaseMethods = array(
            'setProp' => TRUE,
            'delProp' => TRUE,
            'deleteKey' => TRUE,
            'setKey' => TRUE,
            'setType' => TRUE,
            'getAll' => FALSE,
            'getKey' => FALSE,
            'getProp' => FALSE,
            'getType' => FALSE,
        );

        $platformStub = new Test_Tool_MockState();

        foreach ($env->getDatabaseNames() as $database) {
            $dbStub = $env->getDatabase($database);
            $dbMock = $this->getMockBuilder('Nethgui_System_ConfigurationDatabase')
                ->disableOriginalConstructor()
                ->setMethods(array_keys($databaseMethods))
                ->getMock();


            $methodStub = $this->returnMockObject($dbStub);

            // queue db state stub for isFinal() assertions
            $this->dbObjectCheckList[] = array($database, $methodStub);

            foreach (array_keys($databaseMethods) as $method) {
                $dbMock
                    ->expects($this->any())
                    ->method($method)
                    ->will($methodStub);
            }

            $platformStub->set(array('getDatabase', array($database)), $dbMock);
        }


        foreach ($env->getEvents() as $eventExp) {
            if (is_string($eventExp)) {
                $eventExp = array($eventExp, array());
            }

            $systemCommandMockForSignalEvent = $this->getMock('Nethgui_System_ProcessInterface', array('getOutput', 'getExitStatus', 'getOutputArray', 'isExecuted', 'exec', 'addArgument'));

            $platformStub->set(array('signalEvent', array($eventExp[0], $eventExp[1])), $systemCommandMockForSignalEvent);
        }

        $configurationMock->expects($this->any())
            ->method('getDatabase')
            ->will($this->returnMockObject($platformStub));

        $configurationMock->expects($this->exactly(count($env->getEvents())))
            ->method('signalEvent')
            ->will($this->returnMockObject($platformStub));

        $systemCommandMock = $this->getMock('Nethgui_System_ProcessInterface', array('getOutput', 'getExitStatus', 'getOutputArray', 'isExecuted', 'exec', 'addArgument'));
        $configurationMock->expects($this->any())
            ->method('exec')
            ->will(new Test_Tool_SystemCommandExecution($env->getCommands(), $systemCommandMock));

        return $configurationMock;
    }

    protected function createViewMock(Nethgui_Core_ModuleInterface $module, Test_Tool_ModuleTestEnvironment $env)
    {
        return new Nethgui_Core_View($module);
    }

    /**
     *
     * @param Test_Tool_MockState $state
     * @return PHPUnit_Framework_MockObject_Stub
     */
    protected function returnMockObject(Test_Tool_MockState $state)
    {
        return new Test_Tool_MockObject($state);
    }

    /**
     *
     * @param array $a
     * @return PHPUnit_Framework_MockObject_Stub
     */
    protected function returnArrayKeyExists($a)
    {
        return new Test_Tool_ArrayKeyExists($a);
    }

    /**
     *
     * @param array $a
     * @return PHPUnit_Framework_MockObject_Stub
     */
    protected function returnArrayKeyGetRegexp($a)
    {
        return new Test_Tool_ArrayKeyGetRegexp($a);
    }

    /**
     *
     * @param array $a
     * @return PHPUnit_Framework_MockObject_Stub
     */
    protected function returnArrayKeyGet($a)
    {
        return new Test_Tool_ArrayKeyGet($a);
    }

    /**
     *
     *
     * @param type $request
     * @param type $arguments
     * @param type $isSubmitted
     * @return Nethgui_Core_RequestInterface
     */
    protected function createRequestMock(Test_Tool_ModuleTestEnvironment $env)
    {
        $data = $env->getRequest();
        $arguments = $env->getArguments();
        $submitted = $env->isSubmitted();
        $user = $this->createUserMock($env);

        return Test_Tool_ModuleTestCaseCoreRequest::createInstance($user, $data, $submitted, $arguments);
    }

    protected function createUserMock(Test_Tool_ModuleTestEnvironment $env)
    {
        return $this->getMock('Nethgui_Client_UserInterface');
    }

    protected function createValidationReportMock(Test_Tool_ModuleTestEnvironment $env)
    {
        $reportMock = $this->getMockBuilder('Nethgui_Module_NotificationArea')
            ->setConstructorArgs(array($this->createUserMock($env)))
            ->setMethods(array('addValidationError'))
            ->getMock();

        // Check addError() is never called.
        // If you need to check for validation errors
        // override this method to provide another object mock.
        $reportMock->expects($this->never())
            ->method('addValidationError')
            ->withAnyParameters();

        return $reportMock;
    }

}

/**
 * @ignore
 *
 */
class Test_Tool_ArrayKeyGetRegexp implements PHPUnit_Framework_MockObject_Stub
{

    /**
     *
     * @var array
     */
    private $a;

    public function __construct($a)
    {
        $this->a = $a;
    }

    public function invoke(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        $parameterName = array_shift($invocation->parameters);

        foreach ($this->a as $regexp => $returnValue) {
            if (preg_match($regexp, $parameterName) > 0) {
                return $returnValue;
            }
        }

        throw new PHPUnit_Framework_ExpectationFailedException("The requested key `{$parameterName}` does not match any given pattern!");
    }

    public function toString()
    {
        return PHPUnit_Util_Type::toString($this);
    }

}

/**
 * @ignore
 *
 */
class Test_Tool_ArrayKeyGet implements PHPUnit_Framework_MockObject_Stub
{

    /**
     *
     * @var array
     */
    private $a;

    public function __construct($a)
    {
        $this->a = $a;
    }

    public function invoke(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        $parameterName = array_shift($invocation->parameters);

        if (is_array($this->a) && array_key_exists($parameterName, $this->a))
        {
            return $this->a[$parameterName];
        }

        throw new PHPUnit_Framework_ExpectationFailedException("The requested key `{$parameterName}` does not exist!");
    }

    public function toString()
    {
        return PHPUnit_Util_Type::toString($this);
    }

}

/**
 * @ignore
 *
 */
class Test_Tool_ArrayKeyExists implements PHPUnit_Framework_MockObject_Stub
{

    /**
     *
     * @var array
     */
    private $a;

    public function __construct($a)
    {
        $this->a = $a;
    }

    public function invoke(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        $parameterName = array_shift($invocation->parameters);

        if (is_array($this->a) && array_key_exists($parameterName, $this->a))
        {
            return TRUE;
        }

        return FALSE;
    }

    public function toString()
    {
        return PHPUnit_Util_Type::toString($this);
    }

}

/**
 * @ignore
 * @see Nethgui_System_ProcessInterface
 */
class Test_Tool_SystemCommandExecution extends Test_Tool_ArrayKeyGetRegexp
{

    /**
     *
     * @var 
     */
    private $mock;

    public function __construct($a, $mock)
    {
        parent::__construct($a);
        $this->mock = $mock;
    }

    public function invoke(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        $returnData = parent::invoke($invocation);

        if (is_string($returnData)) {
            $returnData = array(0, $returnData);
        }

        $mock = clone $this->mock;

        if ($mock instanceof PHPUnit_Framework_MockObject_MockObject)
        {
            $mock->expects(PHPUnit_Framework_TestCase::any())
                ->method('getOutput')
                ->will(PHPUnit_Framework_TestCase::returnValue($returnData[1]));

            $mock->expects(PHPUnit_Framework_TestCase::any())
                ->method('getOutputArray')
                ->will(PHPUnit_Framework_TestCase::returnValue(explode("\n", $returnData[1])));

            $mock->expects(PHPUnit_Framework_TestCase::any())
                ->method('isExecuted')
                ->will(PHPUnit_Framework_TestCase::returnValue(TRUE));

            $mock->expects(PHPUnit_Framework_TestCase::any())
                ->method('getExitStatus')
                ->will(PHPUnit_Framework_TestCase::returnValue($returnData[0]));

            $mock->expects(PHPUnit_Framework_TestCase::never())
                ->method('exec');

            $mock->expects(PHPUnit_Framework_TestCase::never())
                ->method('addArgument');
        }
        return $mock;
    }

}

class Test_Tool_ModuleTestCaseCoreRequest extends Nethgui_Core_Request
{

    public static function createInstance(Nethgui_Client_UserInterface $user, $data, $submitted, $arguments)
    {
        return new self( $user, $data, $submitted, $arguments);
    }

}