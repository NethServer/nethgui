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
interface NethGui_Core_ValidationReportInterface
{
    /**
     * @param NethGui_Core_ModuleInterface $module
     * @param string $parameterName
     * @param string $message
     */
    public function addValidationError(NethGui_Core_ModuleInterface $module, $parameterName, $message);

    /**
     * Check if a validation error has been added.
     * @return boolean
     */
    public function hasValidationErrors();
}

/**
 * Carries notification messages from modules to the User
 *  
 * A module 
 */
interface NethGui_Core_NotificationCarrierInterface
{       
    const NOTIFY_SUCCESS = 0;
    const NOTIFY_WARNING = 1;
    const NOTIFY_ERROR = 2;
   
    /**
     * 
     * @param NethGui_Core_ModuleInterface $module
     * @param $template Optional - default NULL
     * @param $level Optional - default self::NOTIFY_SUCCESS
     * @return void
     */
    public function showDialog(NethGui_Core_ModuleInterface $module, $template = NULL, $type = self::NOTIFY_SUCCESS);
        
    public function addRedirectOrder(NethGui_Core_ModuleInterface $module, $path = array());
}
