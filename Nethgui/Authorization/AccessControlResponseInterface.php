<?php

/**
 * Nethgui
 *
 * @package Authorization
 */

/**
 * AccessControlRequestInterface.
 *
 * An Nethgui\Authorization\AccessControlRequestInterface implementing object encapsulates the authorization
 * response that can be ``GRANTED`` or ``NOT GRANTED``.
 *
 * @see AccessControlRequestInterface
 * @package Authorization
 */
interface Nethgui\Authorization\AccessControlResponseInterface
{

    /**
     * Get a reference to the original Request.
     * @return Nethgui\Authorization\AccessControlRequestInterface The original Request.
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
