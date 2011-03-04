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
     * Creates a new NethGui_Core_Request object from current web request.
     * @param string $defaultModuleIdentifier
     * @param array $parameters 
     * @return RequestInterface
     */
    static public function getWebRequestInstance($defaultModuleIdentifier, $parameters = array())
    {
        static $instance;

        if ( ! isset($instance)) {
            if ($_SERVER['REQUEST_METHOD'] == 'GET') {
                $data = array($defaultModuleIdentifier => $parameters);
                //
            } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
                if (isset($_SERVER['X_REQUESTED_WITH'])
                    && $_SERVER['X_REQUESTED_WITH'] == 'XMLHttpRequest') {
                    // Ajax POST request.
                    // TODO: decode json query
                    $data = array();
                } else {
                    // Browser POST request.
                    $data = array_merge(array($defaultModuleIdentifier => $parameters), $_POST);
                }
            }

            // TODO: retrieve user state from Session
            $user = new NethGui_Core_AlwaysAuthenticatedUser();

            $instance = new self($user, $data);

            /*
             * Clear global variables
             */
            $_POST = array();
            $_GET = array();
        }

        return $instance;
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
