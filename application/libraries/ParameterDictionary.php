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

    public function hasKey($parameterName)
    {
        return isset($this->data[$parameterName]);
    }

    public function getKeys()
    {
        return array_keys($this->data);
    }

    public function getValue($parameterName)
    {
        return $this->data[$parameterName];
    }

    public function getValueAsArray($parameterName)
    {
        $value = $this->getValue($parameterName);

        if(is_null($value))
        {
            return NULL;
        }
        elseif(is_array($value))
        {
            return array_values($value);
        }
        
        return array($value);
    }

    public function getValueAsParameterDictionary($parameterName)
    {
        return new self($this->getValue($parameterName));
    }

}

?>
