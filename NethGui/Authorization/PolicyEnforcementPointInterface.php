<?php

/**
 * NethGui
 *
 * @package NethGui
 * @subpackage Authorization
 */

/**
 * NethGui_Authorization_PolicyEnforcementPointInterface (PEP)
 *
 * A PEP interface implementing object authorizes access to its resources
 * depending on the responses of another object, implementing
 * PolicyDecisionPointInterface.
 *
 * @see PolicyDecisionPointInterface
 * @package NethGui
 * @subpackage Authorization
 */
interface NethGui_Authorization_PolicyEnforcementPointInterface
{

    /**
     * @return PolicyDecisionPointInterface
     */
    public function getPolicyDecisionPoint();

    /**
     * @param NethGui_Authorization_PolicyDecisionPointInterface $pdp
     */
    public function setPolicyDecisionPoint(NethGui_Authorization_PolicyDecisionPointInterface $pdp);
}

