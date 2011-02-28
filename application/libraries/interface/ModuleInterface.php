<?php
/**
 * NethGui
 *
 * @package ExtensibleApi
 */

/**
 * A ModuleInterface implementation is delegated to receive input parameters,
 * validate, process and (optionally) return an html view of the Module.
 *
 * TODO: interface description.
 * @package ExtensibleApi
 */
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
     * @return bool FALSE, if not yet initialized, TRUE otherwise.
     */
    public function isInitialized();


    /**
     * The Module Identifier is a string that univocally identifies a Module.
     * @return string Returns the unique module identifier
     */
    public function getIdentifier();

    /**
     * Sets the reference to the parent Module.
     * @see ModuleCompositeInterface addChild() operation.
     */
    public function setParent(ModuleInterface $parentModule);

    /**
     * Gets a reference to the parent Module.
     * @return ModuleInterface
     */
    public function getParent();

    /**
     * Gets the Module ``title`` attribute.
     * @return string
     */
    public function getTitle();

    /**
     * Gets the Module ``description`` attribute.
     * @return string
     */
    public function getDescription();

    /**
     * Binds Request parameters to Module internal state.
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
     * @return string An (HTML formatted) string
     */
    public function renderView(Response $response);
}

/**
 * A "Top" Module is a Module displayed on the application main menu.
 * 
 * If it provides the identifier of another Module, it will be placed
 * under that Module.
 *
 * Also a "Top" Module is reachable with a specific URL, unlike other Modules.
 *
 * @package ExtensibleApi
 */
interface TopModuleInterface {

    /**
     * @return string Unique parent module identifier
     */
    public function getParentMenuIdentifier();
}

