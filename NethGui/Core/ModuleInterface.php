<?php
/**
 * @package Core
 */

/**
 * A NethGui_Core_ModuleInterface implementation is delegated to
 *    - receive input parameters (parameter binding),
 *    - validate,
 *    - perform process()-ing,
 *    - prepare view parameters.
 *
 * @package Core
 */
interface NethGui_Core_ModuleInterface extends NethGui_Core_RequestHandlerInterface, NethGui_Core_LanguageCatalogProvider
{
    /**
     * To require a full view refresh
     */
    const VIEW_REFRESH = 0;
    /**
     * To require a partial view update
     */
    const VIEW_UPDATE = 1;

    /**
     * Sets the host configuration Model.
     */
    public function setHostConfiguration(NethGui_Core_HostConfigurationInterface $hostConfiguration);

    /**
     * The Module Identifier is a string that univocally identifies a Module.
     * @return string Returns the unique module identifier
     */
    public function getIdentifier();

    /**
     * Sets the reference to the parent Module.
     * @see NethGui_Core_ModuleCompositeInterface addChild() operation.
     */
    public function setParent(NethGui_Core_ModuleInterface $parentModule);

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
     * After initialization a module...
     */
    public function initialize();

    /**
     * Prevents double initialization.
     * @return bool FALSE, if not yet initialized, TRUE otherwise.
     */
    public function isInitialized();


    /**
     * Prepare view layer data, putting it into $view.
     *
     * @param NethGui_Core_ViewInterface $view The view to put the data into
     * @param integer $mode One of VIEW_UPDATE or VIEW_REFRESH values
     * @see NethGui_Core_ModuleInterface::VIEW_REFRESH
     * @see NethGui_Core_ModuleInterface::VIEW UPDATE
     * @see NethGui_Core_ViewInterface
     */
    public function prepareView(NethGui_Core_ViewInterface $view, $mode);
 
}

/**
 * A "Top" Module is a Module displayed on the application main menu.
 * 
 * If it provides the identifier of another Module, it will be placed
 * under that Module.
 *
 * Also a "Top" Module is reachable with a specific URL, unlike other Modules.
 *
 * @package Core
 *
 */
interface NethGui_Core_TopModuleInterface
{

    /**
     * @return string Unique parent module identifier
     */
    public function getParentMenuIdentifier();
}

