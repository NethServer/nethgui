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
final class AccessControlResponse implements AccessControlResponseInterface
{

    public function __construct(AccessControlRequestInterface $originalRequest, $granted = TRUE, $message = '')
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
