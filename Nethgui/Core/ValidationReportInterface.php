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
interface Nethgui\Core\ValidationReportInterface
{
    /**
     * @param Nethgui\Core\ModuleInterface $module
     * @param string $parameterName
     * @param string The error message template
     * @param array Optional - Arguments to the error message. ${0}, ${1}, ${2}
     */
    public function addValidationErrorMessage(Nethgui\Core\ModuleInterface $module, $parameterName, $message, $args = array());

    /**
     * @param Nethgui\Core\ModuleInterface $module
     * @param string $parameterName
     * @param Nethgui\Core\ValidatorInterface $validator
     */
    public function addValidationError(Nethgui\Core\ModuleInterface $module, $parameterName, Nethgui\Core\ValidatorInterface $validator);

    /**
     * Check if a validation error has been added.
     * @return boolean
     */
    public function hasValidationErrors();
}


