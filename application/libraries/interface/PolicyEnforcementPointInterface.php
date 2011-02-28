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

    /**
     * TODO: verify if `setUser` is consistent with the concept of "PEP".
     * @deprecated
     * @param UserInterface $user;
     */
    public function setUser(UserInterface $user);
    
}

