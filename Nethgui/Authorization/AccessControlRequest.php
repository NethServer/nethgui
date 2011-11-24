<?php

/**
 * Nethgui
 *
 */

namespace Nethgui\Authorization;

/**
 * TODO: describe class
 *
 */
final class AccessControlRequest implements AccessControlRequestInterface
{

    public function __construct(\Nethgui\Client\UserInterface $subject, $resource, $action)
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
