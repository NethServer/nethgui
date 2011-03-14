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
     * @see NethGui_Core_ViewInterface
     * @var int
     */
    private $contentType;

    /**
     * Creates a new NethGui_Core_Request object from current web request.
     * @param string $defaultModuleIdentifier
     * @param array $parameters 
     * @return NethGui_Core_Request
     */
    static public function getWebRequestInstance($defaultModuleIdentifier, $parameters = array())
    {
        static $instance;

        if ( ! isset($instance)) {
            if ($_SERVER['REQUEST_METHOD'] == 'GET') {
                $data = array($defaultModuleIdentifier => $parameters);
                $contentType = NethGui_Core_ViewInterface::HTML;
                //
            } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
                    && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'
                    && $_SERVER['CONTENT_TYPE'] == 'application/json; charset=UTF-8') {
                    // Ajax POST request.
                    // TODO: decode json query
                    $data = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
                    if(is_null($data)) {
                        $data = array();
                    }
                    $contentType = NethGui_Core_ViewInterface::JSON;
                } else {
                    // Browser POST request.
                    $data = array_merge(array($defaultModuleIdentifier => $parameters), $_POST);
                    $contentType = NethGui_Core_ViewInterface::HTML;
                }
            }

            // TODO: retrieve user state from Session
            $user = new NethGui_Core_AlwaysAuthenticatedUser();

            $instance = new self($user, $data, $contentType);

            /*
             * Clear global variables
             */
            $_POST = array();
            $_GET = array();
        }

        return $instance;
    }

    private function __construct(NethGui_Core_UserInterface $user, $data, $contentType)
    {
        if (is_null($data)) {
            $data = array();
        }
        if ( ! is_array($data)) {
            $data = array($data);
        }
        $this->data = $data;
        $this->user = $user;
        $this->contentType = $contentType;
    }

    /**
     * Returns the content type code for Response object constructor.
     * @see NethGui_Core_ViewInterface
     * @return int The content type for Response
     */
    public function getContentType()
    {
        return $this->contentType;
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
        return new self($this->user, $this->getParameter($parameterName), $this->contentType);
    }

    public function getUser()
    {
        return $this->user;
    }

}

?>
