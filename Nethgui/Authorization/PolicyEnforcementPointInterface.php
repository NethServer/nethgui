<?php

/**
 * Nethgui
 *
 * @package Authorization
 */

/**
 * Nethgui\Authorization\PolicyEnforcementPointInterface (PEP)
 *
 * A PEP interface implementing object authorizes access to its resources
 * depending on the responses of another object, implementing
 * PolicyDecisionPointInterface.
 *
 * @see PolicyDecisionPointInterface
 * @package Authorization
 */
interface Nethgui\Authorization\PolicyEnforcementPointInterface
{

    /**
     * @return PolicyDecisionPointInterface
     */
    public function getPolicyDecisionPoint();

    /**
     * @param Nethgui\Authorization\PolicyDecisionPointInterface $pdp
     */
    public function setPolicyDecisionPoint(Nethgui\Authorization\PolicyDecisionPointInterface $pdp);
}

