<?php
/**
 */

namespace Nethgui\Core;

/**
 * UNSTABLE
 * @todo describe interface
 *
 */
interface ValidationReportInterface
{
    /**
     * @param ModuleInterface $module
     * @param string $parameterName
     * @param string The error message template
     * @param array Optional - Arguments to the error message. ${0}, ${1}, ${2}
     */
    public function addValidationErrorMessage(ModuleInterface $module, $parameterName, $message, $args = array());

    /**
     * @param ModuleInterface $module
     * @param string $parameterName
     * @param ValidatorInterface $validator
     */
    public function addValidationError(ModuleInterface $module, $parameterName, ValidatorInterface $validator);

    /**
     * Check if a validation error has been added.
     * @return boolean
     */
    public function hasValidationErrors();
}


