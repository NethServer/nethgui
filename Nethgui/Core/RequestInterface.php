<?php
namespace Nethgui\Core;

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
 * A User Request.
 *
 * A RequestInterface object carries parameters submitted by the
 * User to a Module (and its submodules).
 *
 */
interface RequestInterface
{

    /**
     * Get a parameter value
     * @param string $parameterName
     * @return mixed
     */
    public function getParameter($parameterName);

    /**
     * Spawn a new request.
     *
     * The request will receive $parameterName as data and $arguments as arguments.
     *
     * @param string $parameterName
     * @return RequestInterface
     */
    public function getParameterAsInnerRequest($parameterName, $arguments = array());

    /**
     * Checks if $parameterName exists.
     * @return bool
     */
    public function hasParameter($parameterName);

    /**
     * Get a list of parameter names.
     *
     * Values submitted by the User are called "parameters".
     * @see getModuleArguments()
     * @return array
     */
    public function getParameters();

    /**
     * Values coming from the resource name and query string are called
     * "arguments".
     * 
     * @see getParameters()
     * @return array
     */
    public function getArguments();

    /**
     * Indicates whether the request contains any parameter or no.
     * @return bool
     */
    public function isEmpty();

    /**
     * Indicates whether the request comes from a POST method or no.
     * @return bool
     */
    public function isSubmitted();

    /**
     * The User that has sent the request.
     * @return \Nethgui\Client\UserInterface
     */
    public function getUser();
}
