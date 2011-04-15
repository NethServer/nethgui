<?php
/**
 * @package Tests
 */

/**
 * This is a base abstract class module testing.
 *
 * Usage:
 *  1. initialize member variables to define input and expected outputs
 *  2. call runModuleTestProcedure()
 *
 * Refs #11
 *
 * @package Tests
 */
abstract class ModuleTestCase extends PHPUnit_Framework_TestCase
{
    const DB_SET_KEY = 'setType';
    const DB_GET_KEY = 'getType';
    const DB_DEL_KEY = 'deleteKey';
    const DB_SET_PROP = 'setProp';
    const DB_GET_PROP = 'getProp';
    const DB_DEL_PROP = 'delProp';
    const DB_GET_TYPE = 'getType';
    const DB_SET_TYPE = 'setType';
    const DB_GET_ALL = 'getAll';

    /**
     * @var NethGui_Core_Module_Standard
     */
    protected $object;
    /**
     * Module input parameters in the form name => value
     *
     * @var array
     */
    protected $moduleParameters;
    /**
     * View output parameters. An ordered array of couples <name, value>. Must be
     * in the same order of input parameters.
     *
     * @var array
     */
    protected $expectedView;
    /**
     * An array representing the sequence of operations performd on database.
     * Each element is a n-uple <database, function, args, return_value>.
     *
     * @var array
     */
    protected $expectedDb;
    /**
     * Events triggered by the module. Currently not used.
     * @todo
     * @var array
     */
    protected $expectedEvents;
    /**
     * Controls return value of NethGui_Core_Request::isSubmitted() mock
     * @var bool
     */
    protected $submittedRequest;
    private $databaseMocks;
    /**
     * The string is prefixed to values returned by NethGui_Core_View::buildUrl()
     * @var string
     */
    protected $urlReturnPrefix = 'http://localhost/';

    /**
     * @return NethGui_Core_HostConfigurationInterface
     */
    protected function provideHostConfiguration()
    {
        $configurationMock = $this->getMockBuilder('NethGui_Core_HostConfiguration')
                ->disableOriginalConstructor()
                ->setMethods(array('getDatabase'))
                ->getMock()
        ;

        // Mocking getDatabase():
        // Find which database the test wants to access.
        $alternatives = array();

        foreach ($this->expectedDb as $op) {
            $alternatives[] = $op[0];
        }

        $alternatives = array_unique($alternatives);

        $configurationMock->expects($this->any())
            ->method('getDatabase')
            ->with(call_user_func_array(array($this, 'logicalOr'), $alternatives))
            ->will($this->returnCallback(array($this, 'getMockForConfigurationDatabase')));

        // Mocking signalEvent()
        // TODO: complete signalEvent mocking
        $configurationMock->expects($this->any())
            ->method('signalEvent')
            ->will($this->returnValue(TRUE));

        return $configurationMock;
    }

    /**
     *
     * @param NethGui_Core_ConfigurationDatabase $database
     * @return NethGui_Core_ConfigurationDatabase Mocked
     */
    public function getMockForConfigurationDatabase($database)
    {
        if ( ! isset($this->databaseMocks[$database])) {
            $databaseMock = $this->getMockBuilder('NethGui_Core_ConfigurationDatabase')
                    ->disableOriginalConstructor()
                    ->setMethods(array(
                        'getProp',
                        'setProp',
                        'delProp',
                        'deleteKey',
                        'setKey',
                        'getKey',
                        'getType',
                        'setType',
                        'getAll'))
                    ->getMock();

            $index = 0;
            foreach ($this->expectedDb as $op) {
                if ($op[0] !== $database) {
                    continue;
                }

                $method = $databaseMock->expects($this->at($index))
                        ->method($op[1]);
                $with = call_user_func_array(array($method, 'with'), is_array($op[2]) ? $op[2] : array($op[2]));
                $with->will($this->returnValue($op[3]));
                $index ++;
            }

            $this->databaseMocks[$database] = $databaseMock;
        }

        return $this->databaseMocks[$database];
    }

    /**
     * @param array $requestData
     * @return NethGui_Core_RequestInterface
     */
    protected function provideRequest()
    {
        $requestMock = $this->getMockBuilder("NethGui_Core_Request")
                ->disableOriginalConstructor()
                ->setMethods(array('hasParameter', 'getParameter', 'isEmpty', 'isSubmitted', 'getParameters'))
                ->getMock()
        ;

        if (empty($this->moduleParameters)) {
            $requestMock->expects($this->any())
                ->method('isEmpty')
                ->will($this->returnValue(TRUE));
        } else {
            $requestMock->expects($this->any())
                ->method('isEmpty')
                ->will($this->returnValue(FALSE));
        }

        if ($this->submittedRequest) {
            $requestMock->expects($this->any())
                ->method('isSubmitted')
                ->will($this->returnValue(TRUE));
        } else {
            $requestMock->expects($this->any())
                ->method('isSubmitted')
                ->will($this->returnValue(FALSE));
        }

        $requestMock->expects($this->any())
            ->method('getParameters')
            ->will($this->returnValue(array_keys($this->moduleParameters)));

        $requestMock->expects($this->any())
            ->method('hasParameter')
            ->will($this->returnCallback(array($this, 'requestHasParameter')));

        $requestMock->expects($this->any())
            ->method('getParameter')
            ->will($this->returnCallback(array($this, 'requestGetParameter')));

        return $requestMock;
    }

    public function requestHasParameter($parameter)
    {
        return array_key_exists($parameter, $this->moduleParameters);
    }

    public function requestGetParameter($parameter)
    {
        if ( ! isset($this->moduleParameters[$parameter])) {
            return NULL;
        }
        return $this->moduleParameters[$parameter];
    }

    /**
     * Provide a view mock expecting all parameters are set to expected value.
     *
     * @return NethGui_Core_ViewInterface
     */
    public function spawnView(NethGui_Core_ModuleInterface $module, $expectedOffsetSet)
    {
        $viewMock = $this->getMockBuilder('NethGui_Core_View')
                ->setMethods(array('offsetSet', 'buildUrl', 'spawnView'))
                ->setConstructorArgs(array($module))
                ->getMock()
        ;

        foreach ($expectedOffsetSet as $index => $args) {
            $viewMock->expects($this->at($index))
                ->method('offsetSet')
                ->with($args[0], $args[1]);
        }


        $viewMock->expects($this->any())
            ->method('spawnView')
            ->withAnyParameters()
            ->will($this->returnCallback(array($this, 'spawnView')));


        $viewMock->expects($this->any())
            ->method('buildUrl')
            ->withAnyParameters()
            ->will(new UrlReturn($this->urlReturnPrefix));

        return $viewMock;
    }

    /**
     * Provided mock fails if it receives `addError()` message.
     *
     * @return NethGui_Core_ValidationReport
     */
    protected function provideValidationReport()
    {
        $reportMock = $this->getMockBuilder('NethGui_Core_ValidationReport')
                ->getMock();

        $reportMock->expects($this->never())
            ->method('addError')
            ->withAnyParameters();

        return $reportMock;
    }

    protected function setUp()
    {
        $this->moduleParameters = array();
        $this->expectedView = array();
        $this->expectedDb = array();
        $this->expectedEvents = array();
        $this->databaseMocks = array();
        $this->submittedRequest = TRUE;
    }

    protected function runModuleTestProcedure($viewMode = NethGui_Core_ModuleInterface::VIEW_REFRESH)
    {
        $this->object->setHostConfiguration($this->provideHostConfiguration());
        $this->object->initialize();
        $this->object->bind($this->provideRequest());
        $this->object->validate($this->provideValidationReport());
        $this->object->process();

        $this->object->prepareView($this->spawnView($this->object, $this->expectedView), $viewMode);
    }

}

/**
 * @package Tests
 */
class UrlReturn extends PHPUnit_Framework_MockObject_Stub_Return
{

    /**
     *
     * @var string
     */
    protected $urlPrefix;

    public function __construct($urlPrefix)
    {
        $this->urlPrefix = $urlPrefix;
    }

    public function invoke(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        if (isset($invocation->parameters) && is_array($invocation->parameters)) {
            $parameters = implode('/', $invocation->parameters);
        } else {
            $parameters = '';
        }

        return $this->urlPrefix . $parameters;
    }

    public function toString()
    {
        return sprintf('return url from arguments (%s)', implode(', ', $this->parameters));
    }

}