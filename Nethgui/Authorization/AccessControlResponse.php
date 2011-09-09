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
final class Nethgui_Authorization_AccessControlResponse implements Nethgui_Authorization_AccessControlResponseInterface
{

    public function __construct(Nethgui_Authorization_AccessControlRequestInterface $originalRequest, $granted = TRUE, $message = '')
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