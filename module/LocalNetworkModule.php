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
final class LocalNetworkModule extends StandardModule implements TopModuleInterface {

    public function getTitle()
    {
        return "Local network";
    }

    public function  getDescription()
    {
        return "Placeholder description.";
    }

    public function getParentMenuIdentifier()
    {
        return "SecurityModule";
    }

}