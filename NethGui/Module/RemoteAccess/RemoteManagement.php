<?php
/**
 * NethGui
 *
 * @package Modules
 */

/**
 * TODO: describe class
 *
 * @package Modules
 * @subpackage RemoteAccess
 */
class NethGui_Module_RemoteAccess_RemoteManagement extends NethGui_Core_Module_Standard
{

    private $command = 'NOOP';

    /**
     *
     * @var NethGui_Core_AdapterInterface
     */
    private $validFromAdapter;

    /**
     * @codeCoverageIgnore
     * @return string
     */
    public function getDescription()
    {
        return "Controllo di accesso al server-manager.";
    }

    public function initialize()
    {
        parent::initialize();
        $this->declareParameter('networkAddress', '/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/');
        $this->declareParameter('networkMask', '/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/');

        $this->autosave = FALSE;
        $this->validFromAdapter = $this->getHostConfiguration()->getIdentityAdapter('configuration', 'httpd-admin', 'ValidFrom', ',');
    }

    public function bind(NethGui_Core_RequestInterface $request)
    {
        parent::bind($request);

        if ( $request->hasParameter('networkAddress')
            && $request->hasParameter('networkMask')) {
            $this->command = 'UPDATE';
        } else {

            /*
             * If network parameters are set neither by Request nor by declarations,
             * read values from db.
             */
            $value = $this->validFromAdapter[0];

            if(is_null($value)) {
                $value = '/';
            }
            
            list($networkAddress, $networkMask) = explode('/', $value);
            $this->parameters['networkAddress'] = $networkAddress;
            $this->parameters['networkMask'] = $networkMask;
            $this->command = 'NOOP';
        }

        /*
         * After parameter binding we are sure network parameters are string values.
         */
    }

    /**
     * This implements a GUI behaviour: if both fields are empty we want
     * to DELETE the database key. In this case, we skip normal validation.
     */
    public function validate(NethGui_Core_ValidationReportInterface $report)
    {
        /*
         * Allow clearing both fields to say we want to delete the database key.
         */
        if ($this->parameters['networkAddress'] === ''
            && $this->parameters['networkMask'] === '') {

            /*
             * Substitute database UPDATE action with DELETE.
             */
            if ($this->command == 'UPDATE') {
                $this->command = 'DELETE';
            }

            return;
        }

        parent::validate($report);
    }


    public function process()
    {
        parent::process();

        switch ($this->command) {
            case 'DELETE':
                $this->validFromAdapter->delete();
                $this->validFromAdapter->save();
                break;
            case 'UPDATE':
                $this->validFromAdapter[0] = implode('/', array($this->parameters['networkAddress'], $this->parameters['networkMask']));
                $this->validFromAdapter->save();
                break;
        }
    }

}

