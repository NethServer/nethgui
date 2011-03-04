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
    const HTML = 0;
    const JS = 1;
    const CSS = 2;

    /**
     * Returns an integer representing current response view type.
     *
     * @see NethGui_Core_ResponseInterface::HTML
     * @see NethGui_Core_ResponseInterface::JS
     * @see NethGui_Core_ResponseInterface::CSS
     * @return int Integer corresponding to constants defined by this interface
     */
    public function getViewType();

    /**
     * Returns the fully qualified name of a module parameter
     * @param NethGui_Core_ModuleInterface $module The module owning the parameter
     * @param string $parameterName Name of the parameter
     * @return string Fully qualified module parameter name
     */
    public function getParameterName(NethGui_Core_ModuleInterface $module, $parameterName);

    /**
     * Returns the fully qualified name of a module UI element
     * @param NethGui_Core_ModuleInterface $module The module owning the widget
     * @param string $widgetId The widget identifier
     * @return string Fully qualified widget identifier
     */
    public function getWidgetId(NethGui_Core_ModuleInterface $module, $widgetId);
}

?>
