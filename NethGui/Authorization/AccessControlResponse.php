<?php

/**
 * NethGui
 *
 * @package NethGui
 * @subpackage Authorization
 */

/**
 * TODO: describe class
 *
 * @package NethGui
 * @subpackage Authorization
 */
final class NethGui_Authorization_AccessControlResponse implements NethGui_Authorization_AccessControlResponseInterface
{

    public function __construct(NethGui_Authorization_AccessControlRequestInterface $originalRequest, $granted = TRUE, $message = '')
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