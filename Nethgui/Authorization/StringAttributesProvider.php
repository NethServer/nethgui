<?php
namespace Nethgui\Authorization;

/*
 * Copyright (C) 2012 Nethesis S.r.l.
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
 * Give AuthorizationAttributesProviderInterface to a string value
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @internal
 */
class StringAttributesProvider implements AuthorizationAttributesProviderInterface
{

    /**
     *
     * @var string
     */
    private $value;

    /**
     *
     * @param mixed $str
     */
    public function __construct($o)
    {
        if (is_object($o)) {
            $str = method_exists($o, '__toString') ? strval($o) : get_class($o);
        } else {
            $str = strval($o);
        }

        $this->value = $str;
    }

    public function asAuthorizationString()
    {
        return $this->value;
    }

    public function getAuthorizationAttribute($attributeName)
    {
        return NULL;
    }

}
