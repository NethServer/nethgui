<?php
/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * World module.
 *
 * This is the root of the modules composition.
 *
 * @package NethGuiFramework
 */
final class NethGui_Core_Module_World extends NethGui_Core_Module_Composite
{

    /**
     *
     * @var NethGui_Core_ModuleInterface
     */
    private $currentModule;
    /**
     * @var NethGui_Core_ValidationReport
     */
    private $validationReport;

    public function __construct(NethGui_Core_ModuleInterface $currentModule)
    {
        parent::__construct('');
        $this->currentModule = $currentModule;
    }

    public function initialize()
    {
        parent::initialize();
        $this->addChild($this->currentModule);
        $immutables = array(
            'cssMain' => base_url() . 'css/main.css',
            'js' => array(
                'base' => base_url() . 'js/jquery-1.5.1.min.js',
                'ui' => base_url() . 'js/jquery-ui-1.8.10.custom.min.js',
                'test' => base_url() . 'js/nethgui.js',
            ),
            'currentModule' => $this->currentModule->getIdentifier(),
        );

        foreach ($immutables as $immutableName => $immutableValue) {
            $this->declareImmutable($immutableName, $immutableValue);
        }
    }

    public function validate(NethGui_Core_ValidationReportInterface $report)
    {
        parent::validate($report);
        $this->validationReport = $report;
    }

    /**
     * Override default implementation, skipping non-core child modules if we have
     * validation errors.
     */
    public function process()
    {
        $hasValidationErrors = count($this->validationReport->getErrors()) > 0;

        foreach ($this->getChildren() as $child) {
            // XXX: skip process() call on non-core modules
            if ($hasValidationErrors
                && substr(get_class($child), 0, 20) != 'NethGui_Core_Module_') {
                continue;
            }

            $child->process();
        }
    }
}
