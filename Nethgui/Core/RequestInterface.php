<?php
/**
 */

namespace Nethgui\Core;

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
