<?php

// TODO: rename to RequestSomething (?)
final class Request implements RequestInterface {

    /**
     * @var array
     */
    private $data;

    static public function createInstanceFromServer()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $data = array();
        }
        elseif ($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            if (isset($_SERVER['X_REQUESTED_WITH'])
                    && $_SERVER['X_REQUESTED_WITH'] == 'XMLHttpRequest')
            {
                // TODO: decode json query
                $data = array();
            }
            else
            {
                $data = $_POST;
            }
        }
        return new self($data);
    }

    private function __construct($data = array())
    {
        if (is_null($data))
        {
            $data = array();
        }
        if(!is_array($data))
        {
            $data = array($data);
        }
        $this->data = $data;
    }

    public function hasParameter($parameterName)
    {
        return isset($this->data[$parameterName]);
    }

    public function isEmpty()
    {
        return empty($this->data);
    }

    public function getParameters()
    {
        return array_keys($this->data);
    }

    public function getParameter($parameterName)
    {
        return $this->data[$parameterName];
    }

    public function getParameterAsArray($parameterName)
    {
        $value = $this->getParameter($parameterName);

        if (is_null($value))
        {
            return NULL;
        }
        elseif (is_array($value))
        {
            return array_values($value);
        }

        return array($value);
    }

    public function getParameterAsInnerRequest($parameterName)
    {
        return new self($this->getParameter($parameterName));
    }

}

?>
