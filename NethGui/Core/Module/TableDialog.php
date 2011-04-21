<?php
/**
 * @package Core
 * @subpackage Module
 */

/**
 * @package Core
 * @subpackage Module
 */
class NethGui_Core_Module_TableDialog extends NethGui_Core_Module_Standard
{

    /**
     * @var array
     */
    private $dbSchema;
    /**
     *
     * @var array
     */
    private $actions;

    /**
     * @param string $identifier
     * @param string|array $template See {@link NethGui_Core_View::setTemplate()} method.
     * @param array $dbSchema An array of "reduced" parameter declarations: <paramName, paramValidator, paramSubmitDefault>. First parameter is the "primary key".
     * @param array $actions A list of handled action names.
     */
    public function __construct($identifier, $template, $dbSchema, $actions = array())
    {
        parent::__construct($identifier);
        $this->viewTemplate = $template;
        $this->dbSchema = $dbSchema;
        $this->actions = $actions;
    }

    /**
     * We assume that the parent Module is a DialogDataProviderInterface implementor.
     * @return NethGui_Core_Module_DialogDataProviderInterface
     */
    private function getDialogDataProvider()
    {
        $parentModule = $this->getParent();
        if ( ! $parentModule instanceof NethGui_Core_Module_DialogDataProviderInterface) {
            throw new Exception("Dialog is not correctly bound to its DataProvider");
        }
        return $parentModule;
    }

    public function initialize()
    {
        parent::initialize();
        $this->declareParameter('enabled');
        $this->declareParameter('action');
        foreach ($this->dbSchema as $d) {
            $this->declareParameter($d[0], $d[1], NULL, $d[2]);
        }
    }

    public function bind(NethGui_Core_RequestInterface $request)
    {
        parent::bind($request);

        // Get parameters value from dialog data provider, if it's not a User submission.
        if ( ! $request->isSubmitted()) {
            foreach ($this->getDialogDataProvider()->getDialogData() as $parameterName => $parameterValue) {
                $this->parameters[$parameterName] = $parameterValue;
            }
        }
    }

    /**
     * Set the dialog action
     * @param string $actionName
     */
    public function setAction($actionName)
    {
        if (in_array($actionName, $this->actions)) {
            $this->parameters['action'] = $actionName;
            $this->parameters['enabled'] = TRUE;
        } else {
            $this->parameters['action'] = FALSE;
            $this->parameters['enabled'] = FALSE;
        }
    }

    /**
     * Sends an onDialogSave() message to parent Module, if it exists.
     */
    public function process()
    {
        parent::process();

        if(!$this->parameters['enabled']) {
            return;
        }

        foreach ($this->dbSchema as $fieldDescriptor) {
            $values[$fieldDescriptor[0]] = $this->parameters[$fieldDescriptor[0]];
        }

        $this->getDialogDataProvider()->setDialogData($values);
    }

}