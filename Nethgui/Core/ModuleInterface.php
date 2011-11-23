<?php
/**
 * @package Core
 */

namespace Nethgui\Core;

/**
 * Core module operations
 *
 * A module interface implementation is delegated to
 * - initialize the module (and its submodules)
 * - prepare view parameters
 * - provide module informations
 *
 * @see RequestHandlerInterface
 * @package Core
 */
interface ModuleInterface
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
     * Prepare an help template
     */
    const VIEW_HELP = 2;


    /**
     * Sets the host configuration Model.
     */
    public function setPlatform(\Nethgui\System\PlatformInterface $platform);

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
     * @param ViewInterface $view The view to put the data into
     * @param integer $mode One of VIEW_CLIENT or VIEW_SERVER values
     * @see ModuleInterface::VIEW_SERVER
     * @see ModuleInterface::VIEW_CLIENT
     * @see ViewInterface
     */
    public function prepareView(ViewInterface $view, $mode);


    /**
     * Get module tags for search implementation. Any composite module must take care of getTags children's call.
     * @return array in the form (urlModule, (tag1,tag2..tagn)) rappresenting search tags foreach module 
     */
    public function getTags();
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
interface TopModuleInterface
{

    /**
     * @return string Unique parent module identifier
     */
    public function getParentMenuIdentifier();
}

