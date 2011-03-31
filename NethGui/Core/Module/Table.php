<?php
/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * TODO: describe class
 *
 * @package NethGuiFramework
 * @subpackage StandardImplementation
 */

class NethGui_Core_Module_Table extends NethGui_Core_Module_Composite {

    public function __construct($database, $type)
    {
        parent::__construct();
        $this->database = $database;
        $this->type = $type;
    }

    // FIXME: bind implementation instantiate TableUpdater to perform Create or Update operations.
    public function bind(NethGui_Core_RequestInterface $request)
    {
        parent::bind($request);
        if ( ! $request->isSubmitted()) {
            $action = $request->getParameter('0');
            $key = $request->getParameter('1');
        } else {
            $action = $request->getParameter('action');
            $key = $request->getParameter('key');
        }

        if ($action == 'update') {
            $formModule = new NethGui_Core_Module_KeyUpdater($this->database, $key, $this->type);

        } else {
            $formModule = new NethGui_Core_Module_KeyCreator(
                    $this->database,
                    $this->type,
                    $this->type,

                // FIXME: move to LocalNetwork module.
                    array(
                        array('network', '/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})$/'),
                        array('mask', '/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})$/'),
                        array('router', '/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})$/'),
                    )
            );
        }

        $formModule->setHostConfiguration($this->getHostConfiguration());        
        $formModule->initialize();
        $formModule->bind($request->getParameterAsInnerRequest('network'));
        $this->addChild($formModule);
    }



}