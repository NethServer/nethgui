<?php

/**
 * Nethgui
 *
 * @package Authorization
 */

namespace Nethgui\Authorization;

/**
 * TODO: describe class
 *
 * @package Authorization
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
