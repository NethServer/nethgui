<?php
/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * Implementor of Response Interface supports Modules by giving informations
 * about the current view.
 *
 * @package NethGuiFramework
 */
interface NethGui_Core_ResponseInterface
{
    const JSON = 0;
    const HTML = 1;
    const JS = 2;
    const CSS = 3;

    /**
     * Returns an integer representing current Response type.
     *
     * @see NethGui_Core_ResponseInterface::HTML
     * @see NethGui_Core_ResponseInterface::JSON     
     * @return int Integer corresponding to constants defined by this interface
     */
    public function getFormat();

    /**
     * Returns the fully qualified name of a module parameter
     * @param NethGui_Core_ModuleInterface $module The module owning the parameter
     * @param string $parameterName Name of the parameter
     * @return string Fully qualified module parameter name
     */
    public function getParameterName($parameterName);

    /**
     * Returns the fully qualified name of a module UI element
     * @param NethGui_Core_ModuleInterface $module The module owning the widget
     * @param string $widgetId The widget identifier
     * @return string Fully qualified widget identifier
     */
    public function getWidgetId($widgetId);

    /**
     * Set the View to be applied to this object.
     * @param string
     */
    public function setViewName($viewName);

    /**
     * Specifies the data for the View.
     * @param NethGui_Core_ModuleInterface $module
     * @param array $data
     */
    public function setData($data);

    /**
     * Returns a Response associated with $module.  
     *
     * @return NethGui_Core_ResponseInterface
     */
    public function getInnerResponse(NethGui_Core_ModuleInterface $module);

}

?>
