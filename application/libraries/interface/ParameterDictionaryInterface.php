<?php
interface ParameterDictionaryInterface {

   
    /**
     * @param string $parameterName
     * @return mixed
     */
    public function getValue($parameterName);

    /**
     * @return array
     */
    public function getKeys();
    
}