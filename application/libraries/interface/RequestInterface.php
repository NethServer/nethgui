<?php

// TODO: rename to RequestSomethingInterface (?)
interface RequestInterface {
    /**
     * @param string $parameterName
     * @return mixed
     */
    public function getParameter($parameterName);

    /**
     * Gets given entry as array()
     * @param string $parameterName
     * @return array
     */
    public function getParameterAsArray($parameterName);
    
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

}