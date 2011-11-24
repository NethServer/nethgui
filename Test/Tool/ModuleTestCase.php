<?php
namespace Test\Tool;
class ModuleTestCase extends \PHPUnit_Framework_TestCase
{

    private $dbObjectCheckList = array();

    protected function runModuleTest(\Nethgui\Core\ModuleInterface $module, Test\Tool\ModuleTestEnvironment $env)
    {
        $platform = $this->createPlatformMock($env);
        $module->setPlatform($platform);
        $module->initialize();

        if ($module instanceof \Nethgui\Core\RequestHandlerInterface) {
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

        $this->fullViewOutput = array(); // obsolete: $view->getClientEvents();

        foreach ($this->dbObjectCheckList as $dbStubInfo) {
            $this->assertTrue($dbStubInfo[1]->getState()->isFinal(), sprintf('Database `%s` is not in final state! %s', $dbStubInfo[0], $dbStubInfo[1]->getState()));
        }
    }

    protected function createPlatformMock(Test\Tool\ModuleTestEnvironment $env)
    {
        $platformMock = $this->getMockBuilder('\Nethgui\System\NethPlatform')
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

        $platformStub = new Test\Tool\MockState();

        foreach ($env->getDatabaseNames() as $database) {
            $dbStub = $env->getDatabase($database);
            $dbMock = $this->getMockBuilder('\Nethgui\System\ConfigurationDatabase')
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


        $processInterfaceMethods = array('getOutput', 'getExitStatus', 'getOutputArray', 'isExecuted', 'exec', 'addArgument', 'kill', 'readOutput', 'readExecutionState');

        foreach ($env->getEvents() as $eventExp) {
            if (is_string($eventExp)) {
                $eventExp = array($eventExp, array());
            }

            $systemCommandMockForSignalEvent = $this->getMock('\Nethgui\System\ProcessInterface', $processInterfaceMethods);

            // return a \Nethgui\System\ProcessInterface object
            $platformStub->set(array('signalEvent', array($eventExp[0], $eventExp[1])), $systemCommandMockForSignalEvent);
        }

        $platformMock->expects($this->any())
            ->method('getDatabase')
            ->will($this->returnMockObject($platformStub));

        $platformMock->expects($this->exactly(count($env->getEvents())))
            ->method('signalEvent')
            ->will($this->returnMockObject($platformStub));

        $systemCommandMock = $this->getMock('\Nethgui\System\ProcessInterface', $processInterfaceMethods);
        $platformMock->expects($this->any())
            ->method('exec')
            ->will(new Test\Tool\SystemCommandExecution($env->getCommands(), $systemCommandMock));

        return $platformMock;
    }

    protected function createViewMock(\Nethgui\Core\ModuleInterface $module, Test\Tool\ModuleTestEnvironment $env)
    {
        $translator = $this->getMockBuilder('\Nethgui\Language\Translator')
            ->disableOriginalConstructor()
            ->getMock();


        $translator->expects($this->any())
            ->method('translate')
            ->will($this->returnArgument(0));

        $translator->expects($this->any())
            ->method('getLanguageCode')
            ->will($this->returnValue('en'));

        return new \Nethgui\Client\View($module, $translator);
    }

    /**
     *
     * @param Test\Tool\MockState $state
     * @return PHPUnit_Framework_MockObject_Stub
     */
    protected function returnMockObject(Test\Tool\MockState $state)
    {
        return new Test\Tool\MockObject($state);
    }

    /**
     *
     * @param array $a
     * @return PHPUnit_Framework_MockObject_Stub
     */
    protected function returnArrayKeyExists($a)
    {
        return new Test\Tool\ArrayKeyExists($a);
    }

    /**
     *
     * @param array $a
     * @return PHPUnit_Framework_MockObject_Stub
     */
    protected function returnArrayKeyGetRegexp($a)
    {
        return new Test\Tool\ArrayKeyGetRegexp($a);
    }

    /**
     *
     * @param array $a
     * @return PHPUnit_Framework_MockObject_Stub
     */
    protected function returnArrayKeyGet($a)
    {
        return new Test\Tool\ArrayKeyGet($a);
    }

    /**
     *
     *
     * @param type $request
     * @param type $arguments
     * @param type $isSubmitted
     * @return \Nethgui\Core\RequestInterface
     */
    protected function createRequestMock(Test\Tool\ModuleTestEnvironment $env)
    {
        $data = $env->getRequest();
        $arguments = $env->getArguments();
        $submitted = $env->isSubmitted();
        $user = $this->createUserMock($env);
        return new \Nethgui\Client\Request($user, $data, $submitted, $arguments, array());
    }

    protected function createUserMock(Test\Tool\ModuleTestEnvironment $env)
    {
        return $this->getMock('\Nethgui\Client\UserInterface');
    }

    protected function createValidationReportMock(Test\Tool\ModuleTestEnvironment $env)
    {
        $reportMock = $this->getMockBuilder('\Nethgui\Module\NotificationArea')
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
class Test\Tool\ArrayKeyGetRegexp implements PHPUnit_Framework_MockObject_Stub
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
class Test\Tool\ArrayKeyGet implements PHPUnit_Framework_MockObject_Stub
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

        if (is_array($this->a) && array_key_exists($parameterName, $this->a)) {
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
class Test\Tool\ArrayKeyExists implements PHPUnit_Framework_MockObject_Stub
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

        if (is_array($this->a) && array_key_exists($parameterName, $this->a)) {
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
 * @see \Nethgui\System\ProcessInterface
 */
class Test\Tool\SystemCommandExecution extends Test\Tool\ArrayKeyGetRegexp
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

        if ($mock instanceof PHPUnit_Framework_MockObject_MockObject) {
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

            $mock->expects(PHPUnit_Framework_TestCase::never())
                ->method('readOutput');

            $mock->expects(PHPUnit_Framework_TestCase::never())
                ->method('kill');

            $mock->expects(PHPUnit_Framework_TestCase::never())
                ->method('readExecutionState');
        }
        return $mock;
    }

}

