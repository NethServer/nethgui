<?php
/**
 * NethGui
 *
 * @package NethGui
 * @subpackage Core_Module
 */

/**
 * Validation report.
 *
 * Displays validation error messages.
 *
 * @package NethGui
 * @subpackage Core_Module
 */
class NethGui_Core_Module_ValidationReport extends NethGui_Core_Module_Standard
{

    /**
     *
     * @var NethGui_Core_ValidationReportInterface
     */
    private $report;

    public function __construct(NethGui_Core_ValidationReportInterface $report)
    {
        parent::__construct();
        $this->report = $report;
    }

    public function initialize()
    {
        parent::initialize();
        $this->declareParameter('errors');
    }

    public function process()
    {
        parent::process();
        $this->parameters['errors'] = new ArrayObject();
        foreach ($this->report->getErrors() as $error) {
            list($fieldId, $message, $module) = $error;

            $this->parameters['errors'][] = array($module->getIdentifier() . '.' . $fieldId, $message);
        }
    }

}

