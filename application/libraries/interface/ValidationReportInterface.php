<?php
/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * TODO: describe interface
 *
 * @package NethGuiFramework
 */
interface ValidationReportInterface {
    public function addError($fieldId, $message);

    /**
     * @return array
     */
    public function getErrors();
}
