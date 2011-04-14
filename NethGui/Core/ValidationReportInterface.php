<?php
/**
 * @package NethGui
 * @subpackage Core
 */

/**
 * UNSTABLE
 * @todo describe interface
 *
 * @package NethGui
 * @subpackage Core
 */
interface NethGui_Core_ValidationReportInterface
{
    public function addError(NethGui_Core_ModuleInterface $module, $fieldId, $message);

    /**
     * @return array
     */
    public function getErrors();
}
