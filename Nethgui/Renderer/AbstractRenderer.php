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
 * Transform a view into a string.
 *
 * Module attributes are exposed as translated strings through the usual interface.
 *
 * @see WidgetInterface
 * @see http://en.wikipedia.org/wiki/Decorator_pattern
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
abstract class AbstractRenderer extends ReadonlyView
{

    /**
     * Render the object as a string
     *
     * @api
     * @see getContentType()
     * @see getCharset()
     * @return string
     */
    abstract public function render();

    /**
     * Convert the given hash to the array format accepted from UI widgets as
     * "datasource".
     *
     * @api
     * @param array $h
     * @param boolean $sort -- default FALSE
     * @return array
     */
    public static function hashToDatasource($H, $sort = FALSE)
    {
        $D = array();

        if( ! is_array($H) && ! $H instanceof \Traversable) {
            return $D;
        }

        foreach ($H as $k => $v) {
            if (is_array($v)) {
                $D[] = array(self::hashToDatasource($v, $sort), $k);
            } elseif (is_string($v)) {
                $D[] = array($k, $v);
            }
        }
        
        if($sort === TRUE) {
           usort($D, function($a, $b) {
               return strcasecmp($a[1], $b[1]);
           }); 
        }

        return $D;
    }

    /**
     * Get the view content mime type
     * 
     * EG: application/json
     * 
     * @api
     * @return string
     */
    abstract public function getContentType();

    /**
     * Get the view content charset enconding 
     * 
     * EG: UTF-8
     * 
     * @api
     * @return string
     */
    abstract public function getCharset();

   
}

