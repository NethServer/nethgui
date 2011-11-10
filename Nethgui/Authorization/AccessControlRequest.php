<?php

/**
 * Nethgui
 *
 * @package Authorization
 */

/**
 * TODO: describe class
 *
 * @package Authorization
 */
final class Nethgui_Authorization_AccessControlRequest implements Nethgui_Authorization_AccessControlRequestInterface
{

    public function __construct(Nethgui_Client_UserInterface $subject, $resource, $action)
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