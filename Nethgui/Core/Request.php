<?php
/**
 * @package Core
 */

/**
 * @todo describe class
 *
 * @package Core
 */
class Nethgui_Core_Request implements Nethgui_Core_RequestInterface
{
    const CONTENT_TYPE_JSON = 1;
    const CONTENT_TYPE_HTML = 2;

    /**
     * @var array
     */
    private $data;
    /**
     * @var UserInterface
     */
    private $user;
    /**
     * @var bool
     */
    private $submitted;
    /**
     * @see Nethgui_Core_RequestInterface::getArguments()
     * @var array
     */
    private $arguments;
    private $attributes;

    /**
     * Creates a new Nethgui_Core_Request object from current HTTP request.
     * @param string $defaultModuleIdentifier
     * @param array $parameters 
     * @return Nethgui_Core_Request
     */
    static public function getHttpRequest($arguments)
    {
        static $instance;

        if (isset($instance)) {
            return $instance;
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
            $isXmlHttpRequest = TRUE;
        } else {
            $isXmlHttpRequest = FALSE;
        }

        
        $submitted = FALSE;
        $data = array();
            
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
            $submitted = TRUE;

            if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json; charset=UTF-8') {
                // Decode RAW request
                $data = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);

                if (is_null($data)) {
                    throw new Nethgui_Exception_HttpStatusClientError('Bad Request', 400);
                }
            } else {
                // Use PHP global:
                $data = $_POST;
            }
        }

        // XXX: This is a non-compliant HTTP Accept-header parsing:
        $httpAccept = isset($_SERVER['HTTP_ACCEPT']) ? trim(array_shift(explode(',', $_SERVER['HTTP_ACCEPT']))) : FALSE;

        if ($httpAccept == 'application/json')
            $contentType = self::CONTENT_TYPE_JSON;
        else {
            // Standard  POST request.
            $contentType = self::CONTENT_TYPE_HTML;
        }


        // TODO: retrieve user state from Session
        $user = new Nethgui_Client_AlwaysAuthenticatedUser();

        $instance = new self($user, $data, $submitted, $arguments);

        $instance->attributes = array(
            'XML_HTTP_REQUEST' => $isXmlHttpRequest,
            'CONTENT_TYPE' => $contentType,
        );

        /*
         * Clear global variables
         */
        $_POST = array();
        
        return $instance;
    }

    protected function __construct(Nethgui_Client_UserInterface $user, $data, $submitted, $arguments)
    {
        if (is_null($data)) {
            $data = array();
        }
        if ( ! is_array($data)) {
            $data = array($data);
        }
        $this->user = $user;
        $this->data = $data;
        $this->submitted = (bool) $submitted;
        $this->arguments = $arguments;
    }

    public function hasParameter($parameterName)
    {
        return array_key_exists($parameterName, $this->data);
    }

    public function isEmpty()
    {
        return empty($this->data);
    }

    public function isSubmitted()
    {
        return $this->submitted;
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

    public function getParameterAsInnerRequest($parameterName, $arguments = array())
    {
        return new self($this->user, $this->getParameter($parameterName), $this->submitted, $arguments);
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function getAttribute($name)
    {
        if ( ! isset($this->attributes[$name])) {
            return NULL;
        }

        return $this->attributes[$name];
    }

    public function getContentType()
    {
        return $this->getAttribute('CONTENT_TYPE');
    }

    public function isXmlHttpRequest()
    {
        return $this->getAttribute('XML_HTTP_REQUEST');
    }

}

?>
