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
final class AdministrationModule extends StandardModule implements TopModuleInterface {

    public function getTitle() {
        return "Administration";
    }

    public function  getDescription()
    {
        return "Backup Ripristino, Log etc...";
    }

    public function getParentMenuIdentifier()
    {
        return NULL;
    }

}