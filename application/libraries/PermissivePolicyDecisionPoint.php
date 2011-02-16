<?php

class PermissivePolicyDecisionPoint implements PolicyDecisionPointInterface {

    public function authorizeRequest(AccessControlRequestInterface $request)
    {
        return new PermissiveResponse($request);
    }

}

class PermissiveResponse implements AccessControlResponseInterface {

    /**
     * @var AccessControlRequestInterface
     */
    private $request;

    public function __construct(AccessControlRequestInterface $request)
    {
        $this->request = $request;
    }

    public function getMessage()
    {
        return "Permissive response: have fun!";
    }

    public function getRequest()
    {
        return $this->request;
    }

    /**
     * This ALWAYS returns true!
     * @return bool
     */
    public function isAccessGranted()
    {
        return true;
    }
}