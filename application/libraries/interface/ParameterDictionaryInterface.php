<?php

// TODO: rename to RequestSomethingInterface (?)
interface ParameterDictionaryInterface {
    /**
     * @param string $parameterName
     * @return mixed
     */
    public function getValue($parameterName);

    /**
     * Gets given entry as array()
     * @param string $parameterName
     * @return array
     */
    public function getValueAsArray($parameterName);
    
    /**
     * Gets given entry as array()
     * @param string $parameterName
     * @return ParameterDictionaryInterface
     */
    public function getValueAsParameterDictionary($parameterName);

    /**
     * Checks if $parameterName exists.
     * @return bool
     */
    public function hasKey($parameterName);

    /**
     * @return array
     */
    public function getKeys();

    /**
     * @return string
     */
    public function getAction();
}