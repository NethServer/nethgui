<?php
/**
 * Nethgui
 *
 * @package Core
 */

/**
 * Each module has a view attacched to it during prepareView operation. A view
 * contains generic elements such as strings, numbers or other (inner) views.
 *
 * @package Core
 */
interface Nethgui_Core_ViewInterface extends ArrayAccess, IteratorAggregate
{

    /**
     * Set the template to be applied to this object.
     *
     * - If a string is given, it identifies a PHP Template script
     *   (ie. Nethgui_View_MyTemplate);
     *
     * - If a callback function is given, it is invoked with an array
     *   representing the view state as argument and is expected to return
     *   a string representing the view;
     *
     * - If a boolean FALSE, is given the view is rendered as an empty string.
     *
     * @param string|callback|boolean $template The template converting the view state to a string
     */
    public function setTemplate($template);

    /**
     * @see setTemplate()
     */
    public function getTemplate();

    /**
     * Assign data to the View state.
     * @param $data
     */
    public function copyFrom($data);

    /**
     * Create a new view object associated to $module
     * @param Nethgui_Core_ModuleInterface $module The associated $module
     * @param boolean Optional If TRUE the returned view is added to the current object with key equal to the module identifier
     * @return Nethgui_Core_ViewInterface The new view object, of the same type of the actual.
     */
    public function spawnView(Nethgui_Core_ModuleInterface $module, $register = FALSE);

    /**
     * The module associated to this view.
     * @return Nethgui_Core_ModuleInterface
     */
    public function getModule();

    /**
     * Gets the array of the current module identifier plus all identifiers of
     * the ancestor modules, starting from the root.   
     *
     * @return array
     */
    public function getModulePath();


    public function getModuleUrl($path = array());

    /**
     * Generate a unique identifier for the given $parts. If no parts are given
     * the identifier refers the the module referenced by the view.
     *
     * @param string|array $parts
     * @return string
     */
    public function getUniqueId($parts = '');

    /**
     * Get the target control identifier for the given view member
     * 
     * @see #358
     * @param string $name
     * @return string
     */
    public function getClientEventTarget($name);

    /**
     * A method to translate a message according to the user language preferences.
     *
     *
     * @param string $value
     * @param array $args
     * @return string
     * @see Nethgui_Core_TranslatorInterface::translate()
     * @see getTranslator()
     */
    public function translate($message, $args = array());

    /**
     * Access to the object performing string translations
     *
     * @see translate()
     * @return Nethgui_Core_TranslatorInterface
     */
    public function getTranslator();

    /**
     * Create command objects through the returned factory class instance
     * 
     * @return Nethgui_Core_CommandFactoryInterface
     */
    public function getCommandFactory();
}

?>
