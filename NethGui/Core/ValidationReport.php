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
final class NethGui_Core_ValidationReport implements NethGui_Core_ValidationReportInterface
{

    private $errors = array();

    public function addError(NethGui_Core_ModuleInterface $module, $fieldId, $message)
    {
        $this->errors[] = array($fieldId, $message, $module);
    }

    public function getErrors()
    {
        return $this->errors;
    }

}

