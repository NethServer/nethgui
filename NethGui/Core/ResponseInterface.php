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
     * Returns an integer representing current response view type.
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
     * @param string
     */
    public function setViewName($viewName);

    /**
     * Specifies data related to module for a view.
     * @param NethGui_Core_ModuleInterface $module
     * @param mixed $data
     */
    public function setData($data);

    /**
     *
     * @return NethGui_Core_ResponseInterface
     */
    public function getInnerResponse(NethGui_Core_ModuleInterface $module);

}

?>
