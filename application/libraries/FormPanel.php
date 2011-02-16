<?php

/**
 * A FormPanel object wraps its child panels into a FORM tag.
 */
class FormPanel extends StandardCompositePanel {

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

    public function render()
    {
        $output = parent::render();
        return form_open_multipart($this->action) . $output . form_close();
    }

}