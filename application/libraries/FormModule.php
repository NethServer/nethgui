<?php

/**
 * A FormModule wraps its children into a FORM tag.
 */
class FormModule extends StandardCompositeModule {

    /**
     *
     * @var string
     */
    private $action;

    /**
     *
     * @param string $identifier Panel unique identifier.
     * @param string $action FORM tag action.
     */
    public function __construct($identifier, $action = NULL)
    {
        parent::__construct($identifier);
        $this->action = is_null($action) ? uri_string() : $action;
    }

    protected function decorate($output)
    {
        return form_open_multipart($this->action) . $output . form_close();
    }

}