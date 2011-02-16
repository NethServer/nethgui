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
}

