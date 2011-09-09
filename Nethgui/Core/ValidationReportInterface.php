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
     * @param string $message
     */
    public function addValidationError(Nethgui_Core_ModuleInterface $module, $parameterName, $message);

    /**
     * Check if a validation error has been added.
     * @return boolean
     */
    public function hasValidationErrors();
}


