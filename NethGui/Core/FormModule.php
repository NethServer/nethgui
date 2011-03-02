<?php
/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * A NethGui_Core_FormModule wraps its children into a FORM tag.
 *
 * @package NethGuiFramework
 * @subpackage StandardImplementation
 */
class NethGui_Core_FormModule extends NethGui_Core_StandardModuleComposite {

    /**
     *
     * @var string
     */
    private $action;

    /**
     *
     * @param string $identifier Module unique identifier.
     * @param string $action FORM tag action.
     */
    public function __construct($identifier = NULL, $action = NULL)
    {
        parent::__construct($identifier);
        $this->action = is_null($action) ? uri_string() : $action;
    }

    protected function decorate($output, NethGui_Core_Response $response)
    {
        // TODO: insert CSRF token.
        if ($response->getViewType() === NethGui_Core_Response::HTML)
        {
            return form_open_multipart($this->action) . $output . form_close();
        }
    }

}