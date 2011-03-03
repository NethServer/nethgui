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
final class NethGui_Module_SecurityModule extends NethGui_Core_StandardModule implements NethGui_Core_TopModuleInterface
{

    public function getTitle()
    {
        return "Security";
    }

    public function getParentMenuIdentifier()
    {
        return NULL;
    }

}