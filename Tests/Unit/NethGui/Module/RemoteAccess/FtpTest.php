<?php
/**
 * @package Tests
 * @subpackage Unit
 */

/**
 * @package Tests
 * @subpackage Unit
 */
class NethGui_Module_RemoteAccess_FtpTest extends ModuleTestCase
{

    /**
     * @var NethGui_Module_RemoteAccess_Ftp
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        $this->object = new NethGui_Module_RemoteAccess_Ftp();
    }

    public function testNoParamsDisabledService()
    {
        $this->moduleParameters = array();
        $this->submittedRequest = FALSE;

        $this->expectedView = array(
            array('serviceStatus', 'disabled'),
            array('acceptPasswordFromAnyNetwork', ''),
        );

        $this->expectedDb = array(
            array('configuration', self::DB_GET_PROP, array('ftp', 'status'), 'disabled'),
            array('configuration', self::DB_GET_PROP, array('ftp', 'access'), 'private'),
            array('configuration', self::DB_GET_PROP, array('ftp', 'LoginAccess'), 'private'),
        );

        $this->runModuleTestProcedure();
    }

    public function testEnablePrivateService()
    {
        $this->moduleParameters = array('serviceStatus' => 'localNetwork');
        $this->expectedView = array(
            array('serviceStatus', 'localNetwork'),
            array('acceptPasswordFromAnyNetwork', ''),
        );

        $this->expectedDb = array(
            array('configuration', self::DB_GET_PROP, array('ftp', 'status'), 'disabled'),
            array('configuration', self::DB_GET_PROP, array('ftp', 'access'), 'private'),
            array('configuration', self::DB_GET_PROP, array('ftp', 'LoginAccess'), 'private'),
            array('configuration', self::DB_SET_PROP, array('ftp', array('status' => 'enabled')), TRUE),
        );

        $this->runModuleTestProcedure();
    }

    public function testEnableNormalService()
    {
        $this->moduleParameters = array('serviceStatus' => 'anyNetwork');
        $this->expectedView = array(            
            array('serviceStatus', 'anyNetwork'),
            array('acceptPasswordFromAnyNetwork', ''),
        );

        $this->expectedDb = array(
            array('configuration', self::DB_GET_PROP, array('ftp', 'status'), 'disabled'),
            array('configuration', self::DB_GET_PROP, array('ftp', 'access'), 'private'),
            array('configuration', self::DB_GET_PROP, array('ftp', 'LoginAccess'), 'private'),

            array('configuration', self::DB_SET_PROP, array('ftp', array('status' => 'enabled')), TRUE),
            array('configuration', self::DB_SET_PROP, array('ftp', array('access' => 'public')), TRUE),
        );

        $this->runModuleTestProcedure();
    }

    public function testDisableService()
    {
        $this->moduleParameters = array('serviceStatus' => 'disabled');
        $this->expectedView = array(            
            array('serviceStatus', 'disabled'),
            array('acceptPasswordFromAnyNetwork', ''),
        );


        $this->expectedDb = array(
            array('configuration', self::DB_GET_PROP, array('ftp', 'status'), 'enabled'),
            array('configuration', self::DB_GET_PROP, array('ftp', 'access'), 'public'),
            array('configuration', self::DB_GET_PROP, array('ftp', 'LoginAccess'), 'private'),

            array('configuration', self::DB_SET_PROP, array('ftp', array('status' => 'disabled')), TRUE),
            array('configuration', self::DB_SET_PROP, array('ftp', array('access' => 'private')), TRUE),
        );

        $this->runModuleTestProcedure();
    }

    public function testEnablePassword()
    {
        $this->moduleParameters = array('acceptPasswordFromAnyNetwork' => '1');
        $this->expectedView = array(            
            array('serviceStatus', 'anyNetwork'),
            array('acceptPasswordFromAnyNetwork', '1'),
        );

        $this->expectedDb = array(
            array('configuration', self::DB_GET_PROP, array('ftp', 'status'), 'enabled'),
            array('configuration', self::DB_GET_PROP, array('ftp', 'access'), 'public'),
            array('configuration', self::DB_GET_PROP, array('ftp', 'LoginAccess'), 'private'),

            array('configuration', self::DB_SET_PROP, array('ftp', array('LoginAccess'=>'public')), TRUE),
        );

        $this->runModuleTestProcedure();
    }

    public function testDisablePassword()
    {
        $this->moduleParameters = array();
        $this->expectedView = array(            
            array('serviceStatus', 'anyNetwork'),
            array('acceptPasswordFromAnyNetwork', ''),
        );

        $this->expectedDb = array(
            array('configuration', self::DB_GET_PROP, array('ftp', 'status'), 'enabled'),
            array('configuration', self::DB_GET_PROP, array('ftp', 'access'), 'public'),            
            array('configuration', self::DB_GET_PROP, array('ftp', 'LoginAccess'), 'public'),

            array('configuration', self::DB_SET_PROP, array('ftp', array('LoginAccess'=>'private')), TRUE),
        );

        $this->runModuleTestProcedure();
    }

}

