<?php
/**
 * @author Davide Principi <davide.principi@nethesis.it>
 */

namespace Nethgui\Renderer;

/**
 * Help in the creation of the view output.
 *
 * A widget object can be rendered to a string through 
 *
 * Usually, a widget is associated to a view element by setting its "name" attribute
 * to the key of the view element. 
 *
 * Widgets:
 * - Can be nested in a hierarchical way through the insert() method.
 * - Are configured through the attributes API {set,get,has}Attribute.
 *
 * Basic attribute:
 * - name
 *
 * Implementations can extend the attribute list with their own semantics.
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 */
interface WidgetInterface
{

    /**
     * @param WidgetInterface $widget Another widget to be nested inside the current object
     * @return WidgetInterface the current object
     */
    public function insert(WidgetInterface $widget);

    /**
     * Set the given $attribute to $value
     *
     * @param string $attribute The attribute name
     * @param mixed $value Any value to be assigned to the attribute
     * @return WidgetInterface the current object
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
