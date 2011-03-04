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

    /**
     * @param string $parameterName
     * @return mixed
     */
    public function getParameter($parameterName);

    /**
     * Gets given entry as an inner request object.
     *
     * This
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
     * @return UserInterface
     */
    public function getUser();
}