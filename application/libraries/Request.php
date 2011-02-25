<?php

/**
 * Request.  
 */
final class Request implements RequestInterface {

    /**
     * @var array
     */
    private $data;

    /**
     * @var UserInterface
     */
    private $user;

    /**
     * Create a new Request object from current application state.
     * @param string $defaultModuleIdentifier
     * @return RequestInterface
     */
    static public function createInstanceFromServer($defaultModuleIdentifier)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $data = array($defaultModuleIdentifier => array());
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

        // TODO: retrieve user state from Session
        $user = new AlwaysAuthenticatedUser();
        
        return new self($user, $data);
    }

    private function __construct(UserInterface $user, $data = array())
    {
        if (is_null($data))
        {
            $data = array();
        }
        if ( ! is_array($data))
        {
            $data = array($data);
        }
        $this->data = $data;
        $this->user = $user;
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
        if ( ! isset($this->data[$parameterName]))
        {
            return NULL;
        }
        return $this->data[$parameterName];
    }

    public function getParameterAsInnerRequest($parameterName)
    {
        return new self($this->user, $this->getParameter($parameterName));
    }

    public function __toString()
    {
        $output = '';
        foreach($this->getParameters() as $parameterName)
        {
            $output .= $parameterName . ' = ' . $this->getParameter($parameterName) . ', ';
        }
        return $output;
    }

    public function getUser()
    {
        return $this->user;
    }

}

?>
