<?php
interface PolicyDecisionPointInterface {
    /**
     * @return AccessControlResponseInterface
     */
    public function authorizeRequest(AccessControlRequestInterface $request);
}


