<?php
/**
 * @package Renderer
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * Widgets help in the creation of the XHTML view output.
 *
 * - Widgets can be nested in a hierarchical way through the insert() method.
 * - Widgets are configured through the attributes API {set,get,has}Attribute.
 *
 * @package Renderer
 * @author Davide Principi <davide.principi@nethesis.it>
 */
interface Nethgui_Renderer_WidgetInterface {
    /**
     * @param Nethgui_Renderer_WidgetInterface $widget Another widget to be nested inside the current object
     * @return Nethgui_Renderer_WidgetInterface the current object
     */
    public function insert(Nethgui_Renderer_WidgetInterface $widget);
    /**
     * Set the given $attribute to $value
     *
     * @param string $attribute The attribute name
     * @param mixed $value Any value to be assigned to the attribute
     * @return Nethgui_Renderer_WidgetInterface the current object
     */
    public function setAttribute($attribute, $value);
    /**
     * Checks if the widget has the given $attribute
     *
     * @param string $attribute The attribute name
     * @return boolean TRUE, if the $attribute has been set to any value
     */
    public function hasAttribute($attribute);
    /**
     * Read an attribute value.
     * 
     * If the attribute has not been set return the given default value
     *
     * @param string $attribute The attribute name
     * @param mixed $default The default value
     */
    public function getAttribute($attribute, $default = NULL);
    /**
     * Transform the current object in a string value.
     *
     * @return string
     */
    public function render();
}
