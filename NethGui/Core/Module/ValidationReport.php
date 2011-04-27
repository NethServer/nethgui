<?php
/**
 * NethGui
 *
 * @package Core
 * @subpackage Module
 */

/**
 * Validation report.
 *
 * Displays validation error messages.
 *
 * @package Core
 * @subpackage Module
 */
class NethGui_Core_Module_ValidationReport extends NethGui_Core_Module_Abstract
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

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);

        $errors = $this->report->getErrors();

        $view['errors'] = new ArrayObject();

        foreach ($errors as $error) {
            list($fieldId, $message, $module) = $error;

            $view['errors'][] = array($module->getIdentifier() . '.' . $fieldId, $message);
        }
    }

}

