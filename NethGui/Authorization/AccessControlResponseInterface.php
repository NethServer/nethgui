<?php

/**
 * NethGui
 *
 * @package Authorization
 */

/**
 * AccessControlRequestInterface.
 *
 * An NethGui_Authorization_AccessControlRequestInterface implementing object encapsulates the authorization
 * response that can be ``GRANTED`` or ``NOT GRANTED``.
 *
 * @see AccessControlRequestInterface
 * @package Authorization
 */
interface NethGui_Authorization_AccessControlResponseInterface
{

    /**
     * Get a reference to the original Request.
     * @return NethGui_Authorization_AccessControlRequestInterface The original Request.
     */
    public function getRequest();

    /**
     * @return bool TRUE, if granted, FALSE otherwise.
     */
    public function isAccessGranted();

    /**
     * Can contain a message explaining the response state.
     * @return string
     */
    public function getMessage();
}
