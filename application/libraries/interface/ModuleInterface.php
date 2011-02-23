<?php

interface ModuleInterface {

    /**
     * After initialization a Module must be ready to receive bind(), validate()
     * and render() messages.
     */
    public function initialize();

    /**
     * Prevents double initialization.
     */
    public function isInitialized();


    /**
     * @return string Unique module identifier
     */
    public function getIdentifier();

    /**
     * @see ModuleCompositeInterface addChild() operation.
     */
    public function setParent(ModuleInterface $parentModule);

    /**
     * @return ModuleInterface
     */
    public function getParent();

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

    /**
     * @return string
     */
    public function render();
}

interface TopModuleInterface {

    /**
     * @return string Unique parent module identifier
     */
    public function getParentMenuIdentifier();
}
