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
     * Binds parameters to Module internal state.
     * @param ParameterDictionaryInterface $parameters
     */
    public function bind(ParameterDictionaryInterface $parameters);

    /**
     * Validate input data. Messages are sent to $report.
     * @return void
     */
    public function validate(ValidationReportInterface $report);

    /**
     * @return string
     */
    public function render();
}

interface ValidationReportInterface {
    public function addError($fieldId, $message);

    /**
     * @return array
     */
    public function getErrors();
}


interface TopModuleInterface {

    /**
     * @return string Unique parent module identifier
     */
    public function getParentMenuIdentifier();
}
