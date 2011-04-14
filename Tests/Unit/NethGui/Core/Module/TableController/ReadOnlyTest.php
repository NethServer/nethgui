<?php

class NethGui_Core_Module_TableReadOnlyTest extends ModuleTestCase
{

    protected function setUp()
    {
        parent::setUp();

        $columns = array('network', 'Mask', 'Router', 'SystemLocalNetwork');

        $this->object = new NethGui_Core_Module_TableController(get_class(), 'networks', 'network', $columns);

        $databaseData = array(
            '192.168.1.0' => array(
                'type' => 'network',
                'Mask' => '255.255.255.0',
                'Router' => '192.168.1.254',
                'SystemLocalNetwork' => NULL,
            ),
            '192.168.2.0' => array(
                'type' => 'network',
                'Mask' => '255.255.255.0',
                'Router' => NULL,
                'SystemLocalNetwork' => 'yes',
            ),
        );

        $this->moduleParameters = array();

        $this->expectedDb = array(
            array('networks', self::DB_GET_ALL, array('network'), $databaseData),
        );

        $this->expectedView = array(
            array('action', 'read'),
            array('key', NULL),
            array('page', 0),
            array('size', 20),
            array('rows', array(
                    array('192.168.1.0', '255.255.255.0', '192.168.1.254', NULL),
                    array('192.168.2.0', '255.255.255.0', NULL, 'yes'),
            )),
            array('columns', array(
                    'network',
                    'Mask',
                    'Router',
                    'SystemLocalNetwork'
                )
            ),
        );
    }

    public function testRead1()
    {
        $this->submittedRequest = FALSE;
        $this->runModuleTestProcedure();
    }

    public function testRead2()
    {
        $this->runModuleTestProcedure();
    }

}