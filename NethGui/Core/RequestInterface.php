<?php
/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * A RequestInterface object carries parameters to a Module (and its submodules).
 *
 * @package NethGuiFramework
 */
interface RequestInterface {
    /**
     * @param string $parameterName
     * @return mixed
     */
    public function getParameter($parameterName);
    
    /**
     * Gets given entry as array()
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