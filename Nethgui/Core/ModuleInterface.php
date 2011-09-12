<?php
/**
 * @package Core
 */

/**
 * Core module operations
 *
 * A module interface implementation is delegated to
 * - initialize the module (and its submodules)
 * - prepare view parameters
 * - provide module informations
 *
 * @see Nethgui_Core_RequestHandlerInterface
 * @package Core
 */
interface Nethgui_Core_ModuleInterface
{
    /**
     * Prepare the server view
     */
    const VIEW_SERVER = 0;
    /**
     * Prepare the client view
     */
    const VIEW_CLIENT = 1;

    /**
     * Sets the host configuration Model.
     */
    public function setHostConfiguration(Nethgui_Core_HostConfigurationInterface $hostConfiguration);

    /**
     * The Module Identifier is a string that univocally identifies a Module.
     * @return string Returns the unique module identifier
     */
    public function getIdentifier();

    /**
     * Sets the reference to the parent Module.
     * @see Nethgui_Core_ModuleCompositeInterface addChild() operation.
     */
    public function setParent(Nethgui_Core_ModuleInterface $parentModule);

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
     * @param Nethgui_Core_ViewInterface $view The view to put the data into
     * @param integer $mode One of VIEW_CLIENT or VIEW_SERVER values
     * @see Nethgui_Core_ModuleInterface::VIEW_SERVER
     * @see Nethgui_Core_ModuleInterface::VIEW_CLIENT
     * @see Nethgui_Core_ViewInterface
     */
    public function prepareView(Nethgui_Core_ViewInterface $view, $mode);
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
interface Nethgui_Core_TopModuleInterface
{

    /**
     * @return string Unique parent module identifier
     */
    public function getParentMenuIdentifier();
}

