<?php

final class AlwaysAuthenticatedUser implements UserInterface {

    private $credentials;

    public function getCredential($credentialName)
    {
        if ( ! isset($this->credentials[$credentialName]))
        {
            return NULL;
        }
        return $this->credentials[$credentialName];
    }

    public function getCredentials()
    {
        return array_keys($this->credentials);
    }

    public function isAuthenticated()
    {
        return TRUE;
    }

    public function setAuthenticated($status)
    {
        
    }

    public function setCredential($credentialName, $credentialValue)
    {
        $this->credentials[$credentialName] = $credentialValue;
    }

    public function hasCredential($credentialName)
    {
        return isset($this->credentials[$credentialName]);
    }

}
