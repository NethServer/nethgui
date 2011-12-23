<?php
namespace Nethgui\Renderer;

/*
 * Copyright (C) 2011 Nethesis S.r.l.
 * 
 * This script is part of NethServer.
 * 
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */

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
 * @since 1.0
 * @api
 */
interface WidgetInterface
{

    /**
     * @param \Nethgui\Renderer\WidgetInterface $widget Another widget to be nested inside the current object
     * @return \Nethgui\Renderer\WidgetInterface the current object
     */
    public function insert(WidgetInterface $widget);

    /**
     * Set the given $attribute to $value
     *
     * @param string $attribute The attribute name
     * @param mixed $value Any value to be assigned to the attribute
     * @return \Nethgui\Renderer\WidgetInterface the current object
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
     * If the attribute has not been set return the given default value.
     *
     * The second argument can also be a callable. In this case, it is invoked only
     * if a default value is needed, and is expected to return that value. The
     * attribute name will be passed as first function argument.
     *
     * @param string $attribute The attribute name
     * @param mixed $default The default value or callable closure/function.
     */
    public function getAttribute($attribute, $default = NULL);

    /**
     * Transform the current object in a string value.
     *
     * @return string
     */
    public function render();
}
