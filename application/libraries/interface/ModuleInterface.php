<?php

interface ModuleInterface {

    /**
     * Sets the host configuration Model.
     */
    public function setHostConfiguration(HostConfigurationInterface $hostConfiguration);

    /**
     * After initialization a Module must be ready to receive bind(), validate()
     * process() and renderView() messages.
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
     * Binds Reqiest parameters to Module internal state.
     * @param RequestInterface $request
     */
    public function bind(RequestInterface $request);

    /**
     * Validate input data. Errors are sent to $report.
     * @return void
     */
    public function validate(ValidationReportInterface $report);

    /**
     * Performs Module logics.
     */
    public function process();

    /**
     * Returns the Module view contents.
     * @return string
     */
    public function renderView(Response $response);
}

interface TopModuleInterface {

    /**
     * @return string Unique parent module identifier
     */
    public function getParentMenuIdentifier();
}

