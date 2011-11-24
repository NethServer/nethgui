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
 * @see WidgetInterface
 * @see http://en.wikipedia.org/wiki/Decorator_pattern
 */
abstract class AbstractRenderer extends ReadonlyView
{

    abstract protected function render();

    public function __toString()
    {
        try {
            return $this->render();
        } catch (Exception $ex) {
            $this->getLog()->exception($ex, TRUE);
        }
        return '';
    }

    /**
     * Convert the given hash to the array format accepted from UI widgets as
     * "datasource".
     *
     * @param array $h
     * @return array
     */
    public static function hashToDatasource($H)
    {
        $D = array();

        foreach ($H as $k => $v) {
            if (is_array($v)) {
                $D[] = array(self::hashToDatasource($v), $k);
            } elseif (is_string($v)) {
                $D[] = array($k, $v);
            }
        }

        return $D;
    }

}

