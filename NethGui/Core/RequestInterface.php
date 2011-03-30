<?php

/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * A NethGui_Core_RequestInterface object carries parameters to a Module (and its submodules).
 *
 * @package NethGuiFramework
 */
interface NethGui_Core_RequestInterface
{
    const CONTENT_TYPE_JSON = 1;
    const CONTENT_TYPE_HTML = 2;
    
    /**
     * @param string $parameterName
     * @return mixed
     */
    public function getParameter($parameterName);

    /**
     * Gets given parameter as an inner request object.
     *
     * @param string $parameterName
     * @return RequestInterface
     */
    public function getParameterAsInnerRequest($parameterName);

    /**
     * Checks if $parameterName exists.
     * @return bool
     */
    public function hasParameter($parameterName);

    /**
     * @return array
     */
    public function getParameters();

    /**
     * @return bool
     */
    public function isEmpty();

    /**
     * @return bool
     */
    public function isSubmitted();

    /**
     * @return UserInterface
     */
    public function getUser();
}