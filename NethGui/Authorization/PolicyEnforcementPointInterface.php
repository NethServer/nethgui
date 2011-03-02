<?php
/**
 * NethGui
 *
 * @package Authorization
 */

/**
 * PolicyEnforcementPointInterface (PEP)
 *
 * A PEP interface implementing object authorizes access to its resources
 * depending on the responses of another object, implementing
 * PolicyDecisionPointInterface.
 *
 * @see PolicyDecisionPointInterface
 * @package Authorization
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

