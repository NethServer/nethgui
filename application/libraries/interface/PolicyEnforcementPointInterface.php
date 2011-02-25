<?php

/**
 * 
 */
interface PolicyEnforcementPointInterface {
    /**
     * @return PolicyDecisionPointInterface
     */
    public function getPolicyDecisionPoint();

    /**
     * @param PolicyDecisionPointInterface $pdp
     */
    public function setPolicyDecisionPoint(PolicyDecisionPointInterface $pdp);

    // TODO: verify if `setUser` is consistent with the concept of "PEP".
    public function setUser(UserInterface $user);
    
}

