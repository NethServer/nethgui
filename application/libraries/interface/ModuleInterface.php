<?php
interface ModuleInterface extends RecursiveIterator {
    public function getTitle();
    public function getDescription();

    /**
     * @return ModuleInterface
     */
    public function getModules();

    /**
     * @return PanelInterface
     */
    public function getPanels();
}

