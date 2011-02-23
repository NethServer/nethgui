<?php

final class ParameterDictionary implements ParameterDictionaryInterface {

    /**
     * @var array
     */
    private $data;

    public function __construct($data = array())
    {
        $this->data = $data;
    }

    public function getKeys()
    {
        return array_keys($this->data);
    }

    public function getValue($parameterName)
    {
        if ( ! isset($this->data[$parameterName]))
        {
            return NULL;
        }
        return $this->data[$parameterName];
    }
}

?>
