<?php

/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * TODO: describe class
 *
 * @package NethGuiFramework
 * @subpackage StandardImplementation
 */
final class NethGui_Core_Request implements NethGui_Core_RequestInterface
{

    /**
     * @var array
     */
    private $data;
    /**
     * @var UserInterface
     */
    private $user;

    /**
     * Create a new NethGui_Core_Request object from current application state.
     * @param string $defaultModuleIdentifier
     * @param array $parameters 
     * @return RequestInterface
     */
    static public function createInstanceFromServer($defaultModuleIdentifier, $parameters = array())
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $data = array($defaultModuleIdentifier => $parameters);
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_SERVER['X_REQUESTED_WITH'])
                && $_SERVER['X_REQUESTED_WITH'] == 'XMLHttpRequest') {
                // TODO: decode json query
                $data = array();
            } else {
                $data = array_merge($parameters, $_POST);
            }
        }

        // TODO: retrieve user state from Session
        $user = new NethGui_Core_AlwaysAuthenticatedUser();

        return new self($user, $data);
    }

    private function __construct(NethGui_Core_UserInterface $user, $data = array())
    {
        if (is_null($data)) {
            $data = array();
        }
        if ( ! is_array($data)) {
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
        if ( ! isset($this->data[$parameterName])) {
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
        foreach ($this->getParameters() as $parameterName) {
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
