<?php
namespace Nethgui\System;

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
 * Test if an input value is grammatically and/or logically valid
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
interface ValidatorInterface {

    /**
     * Test if $value is accepted by this validator.
     *
     * @api
     * @param mixed $value
     * @return boolean
     */
    public function evaluate($value);

    /**
     * Tells why validation failed
     * 
     * If evaluate() returns FALSE the validator object must return
     * an explanation of the problem as an array of arrays.
     * 
     * @api
     * @see evaluate()
     * @return array An array of arrays of two elements: a template string and an array of arguments, to invoke strtr().
     */
    public function getFailureInfo();
}
