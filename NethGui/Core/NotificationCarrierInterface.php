<?php
/**
 * @package Core
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * @package Core
 */

/**
 * Carries notification messages from modules to the User
 *  
 * A module 
 */
interface NethGui_Core_NotificationCarrierInterface
{                          
    public function addRedirectOrder(NethGui_Core_ModuleInterface $module, $path = array());
}