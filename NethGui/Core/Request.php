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
class NethGui_Core_Request implements NethGui_Core_RequestInterface
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
     * @see NethGui_Core_RequestInterface
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
                $contentType = self::CONTENT_TYPE_HTML;
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
                    $contentType = self::CONTENT_TYPE_JSON;
                } else {
                    // Browser POST request.
                    $data = array_merge(array($defaultModuleIdentifier => $parameters), $_POST);
                    $contentType = self::CONTENT_TYPE_HTML;
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
     * Returns the content type requested by the client
     * @see NethGui_Core_RequestInterface
     * @return int The content type code corresponding to HTML or JSON HTTP content-type
     */
    public function getContentType()
    {
        return $this->contentType;
    }


    public function hasParameter($parameterName)
    {
        return array_key_exists($parameterName, $this->data);
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
