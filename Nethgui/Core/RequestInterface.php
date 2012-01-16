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
 * @api
 */
interface RequestInterface
{
    const CONTENT_TYPE_UNSPECIFIED = 0;
    const CONTENT_TYPE_JSON = 1;
    const CONTENT_TYPE_HTML = 2;

    /**
     * Get a parameter value
     * @param string $parameterName
     * @return mixed
     * @api
     */
    public function getParameter($parameterName);

    /**
     * Get an argument value
     * @param string $parameterName
     * @return mixed
     * @api
     */
    public function getArgument($argumentName);

    /**
     * Spawn a new request.
     *
     * The request will receive a subset of parameters and arguments according
     * to the $subsetName parameter.
     *
     * @param string $subsetName The name of parameters/arguments
     * @return RequestInterface
     * @api
     */
    public function spawnRequest($subsetName, $path = array());

    /**
     * Checks if $parameterName exists in the request
     *
     * @param string $parameterName
     * @return bool
     * @api
     */
    public function hasParameter($parameterName);

    /**
     * Checks if $argumentName exists in the request
     *
     * @param string $argumentName
     * @return bool
     * @api
     */
    public function hasArgument($argumentName);

    /**
     * Get a list of parameter names.
     *
     * Values submitted by the User are called "parameters".
     * @see getModuleArguments()
     * @return array
     * @api
     */
    public function getParameterNames();

    /**
     * Get a list of argument names
     *
     * @return array
     * @api
     */
    public function getArgumentNames();

    /**
     * URL (sub)path segments
     * 
     * @see getParameters()
     * @return array
     * @api
     */
    public function getPath();

    /**
     * Indicates whether the request contains any parameter or not.
     * @return bool
     * @api
     */
    public function isEmpty();

    /**
     * Indicates whether the request comes from a POST method or not.
     * @return bool
     * @api
     */
    public function isSubmitted();

    /**
     * Tells if a request has been successfully validated
     * @return bool
     * @api
     */
    public function isValidated();

    /**
     * The User that has sent the request.
     * @return \Nethgui\Core\UserInterface
     * @api
     */
    public function getUser();

    /**
     * Get the "file extension" of the request.
     *
     * "File extension" is the substring after the last "." character in the
     * URL path.
     * @return string
     */
    public function getExtension();
}
