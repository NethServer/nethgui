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
final class NethGui_Module_AdministrationModule extends NethGui_Core_StandardModule implements NethGui_Core_TopModuleInterface {

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