<?php

/**
 * Nethgui
 *
 * @package Authorization
 */

namespace Nethgui\Authorization;

/**
 * AccessControlRequestInterface.
 *
 * An AccessControlRequestInterface implementing object encapsulates the authorization
 * response that can be ``GRANTED`` or ``NOT GRANTED``.
 *
 * @see AccessControlRequestInterface
 * @package Authorization
 */
interface AccessControlResponseInterface
{

    /**
     * Get a reference to the original Request.
     * @return AccessControlRequestInterface The original Request.
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
