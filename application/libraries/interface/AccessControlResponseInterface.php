<?php
/**
 * NethGui
 *
 * @package Authorization
 */

/**
 * TODO: describe interface
 *
 * @package Authorization
 */
interface AccessControlResponseInterface {
    /**
     * @return AccessControlRequestInterface
     */
    public function getRequest();

    /**
     * @return bool
     */
    public function isAccessGranted();

    /**
     * @return string
     */
    public function getMessage();
}
