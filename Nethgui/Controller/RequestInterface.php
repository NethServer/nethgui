<?php
namespace Nethgui\Controller;

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
 * A Request is the input data to process
 *
 * @api
 * @since 1.0
 * @author Davide Principi <davide.principi@nethesis.it>
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
     * @deprecated
     */
    public function getArgument($argumentName);

    /**
     * Spawn a new request.
     *
     * The request will receive a subset of parameters and arguments according
     * to the $subsetName parameter.
     *
     * @param string $subsetName The name of parameters/arguments
     * @param array $path URL path segments values
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
     * @deprecated
     */
    public function hasArgument($argumentName);

    /**
     * Get a list of parameter names.
     *
     * Values submitted by the User are called "parameters".
     * @see getModuleArguments()
     * @return array
     * @deprecated
     */
    public function getParameterNames();

    /**
     * Get a list of argument names
     *
     * @return array
     * @deprecated
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
     * 
     * @return bool
     * @api
     */
    public function isEmpty();

    /**
     * Indicates whether the request is a mutation request
     * 
     * @return bool
     * @api
     */
    public function isMutation();

    /**
     * Tells if a request has been successfully validated
     *
     * @return bool
     * @api
     */
    public function isValidated();

    /**
     * The User that has sent the request.
     *
     * @api
     * @return \Nethgui\Authorization\UserInterface
     */
    public function getUser();

    /**
     * Get the "file extension" of the request.
     *
     * "File extension" is the substring after the last "." character in the
     * URL path.
     *
     * @deprecated Use getFormat()
     * @return string
     */
    public function getExtension();

    /**
     * The requested output language, used to build the Translator
     *
     * @api
     * @return string
     */
    public function getLanguageCode();

    /**
     * The requested output format, used to build the Renderer object
     *
     * @api
     * @return string
     */
    public function getFormat();
}
