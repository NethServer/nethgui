<?php
/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * UserInterface provides access to the login information of the current user.
 *
 * @package NethGuiFramework
 */
interface UserInterface {
    /**
     * @return boolean
     */
    public function isAuthenticated();

    /**
     * @param bool $status
     */
    public function setAuthenticated($status);

    /**
     * @param string $credentialName
     * @param mixed $credentialValue
     */
    public function setCredential($credentialName, $credentialValue);

    /**
     * @param string $credentialName
     * @return mixed
     */
    public function getCredential($credentialName);

    /**
     * @return array
     */
    public function getCredentials();

    public function hasCredential($credentialName);
}



