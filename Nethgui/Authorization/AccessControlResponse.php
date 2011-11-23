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
final class Nethgui\Authorization\AccessControlResponse implements Nethgui\Authorization\AccessControlResponseInterface
{

    public function __construct(Nethgui\Authorization\AccessControlRequestInterface $originalRequest, $granted = TRUE, $message = '')
    {
        $this->originalRequest = $originalRequest;
        $this->message = $message;
        $this->granted = $granted;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getRequest()
    {
        return $this->originalRequest;
    }

    public function isAccessGranted()
    {
        return $this->granted;
    }

}