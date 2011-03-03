<?php

/**
 * NethGui
 *
 * @package Authorization
 */

/**
 * TODO: describe class
 *
 * @package Authorization
 */
final class NethGui_Authorization_AccessControlRequest implements NethGui_Authorization_AccessControlRequestInterface
{

    public function __construct(NethGui_Core_UserInterface $subject, $resource, $action)
    {
        $this->subject = $subject;
        $this->action = $action;
        $this->resource = $resource;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function getSubject()
    {
        return $this->subject;
    }

}