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

