<?php
interface ModuleCompositeInterface {
    /**
     * @return array An array of ModuleInterface implementing objects.
     */
    public function getChildren();
    
    public function createChildren();

    /**
     *
     */
    public function addChild(ModuleInterface $module);

}

