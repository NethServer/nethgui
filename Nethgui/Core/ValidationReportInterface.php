<?php
/**
 * @package Core
 */

/**
 * UNSTABLE
 * @todo describe interface
 *
 * @package Core
 */
interface Nethgui_Core_ValidationReportInterface
{
    /**
     * @param Nethgui_Core_ModuleInterface $module
     * @param string $parameterName
     * @param string The error message template
     * @param array Optional - Arguments to the error message. ${0}, ${1}, ${2}
     */
    public function addValidationErrorMessage(Nethgui_Core_ModuleInterface $module, $parameterName, $message, $args = array());

    public function addValidationError(Nethgui_Core_ModuleInterface $module, $parameterName, Nethgui_Core_ValidatorInterface $validator);

    /**
     * Check if a validation error has been added.
     * @return boolean
     */
    public function hasValidationErrors();
}


