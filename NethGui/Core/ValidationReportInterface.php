<?php

/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * UNSTABLE
 * TODO: describe interface
 *
 * @package NethGuiFramework
 */
interface NethGui_Core_ValidationReportInterface
{
    public function addError(NethGui_Core_ModuleInterface $module, $fieldId, $message);

    /**
     * @return array
     */
    public function getErrors();
}
