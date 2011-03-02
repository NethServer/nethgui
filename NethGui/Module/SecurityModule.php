<?php
/**
 * NethGui
 *
 * @package Modules
 */

/**
 * TODO: describe class
 *
 * @package Modules 
 */
final class SecurityModule extends StandardModule implements TopModuleInterface {

    public function getTitle() {
        return "Security";
    }

    public function getParentMenuIdentifier()
    {
        return NULL;
    }

}