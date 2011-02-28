<?php
/**
 * NethGui
 *
 * @package Authorization
 */

/**
 * AccessControlRequestInterface.
 *
 * An AccessControlRequestInterface implementing object represents a request
 * originating from a Subject to perform a specific Action on a given Resource.
 *
 * @see AccessControlResponseInterface
 * @package Authorization
 */
interface AccessControlRequestInterface {

    /**
     * @return UserInterface
     */
    public function getSubject();

    /**
     * @return string
     */
    public function getResource();

    /**
     * @return string
     */
    public function getAction();
}

