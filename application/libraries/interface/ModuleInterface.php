<?php

interface ModuleInterface {

    /**
     * @return string Unique module identifier
     */
    public function getIdentifier();

    /**
     * @return ModuleInterface
     */
    public function getParent();

    /**
     * TODO: see if can remove this method and leave it to implementation
     * @param ModuleInterace $parent
     */
    public function setParent(ModuleInterface $parent);


    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param array $parameters
     */
    public function bind($inputParameters);

    /**
     * @return boolean
     */
    public function validate();

    public function render();
}


interface ModuleMenuInterface {
    /**
     * @return string Unique parent module identifier
     */
    public function getParentMenuIdentifier();
    
}
