<?php

/**
 * Nethgui
 *
 * @package Authorization
 */

/**
 * Nethgui_Authorization_PolicyEnforcementPointInterface (PEP)
 *
 * A PEP interface implementing object authorizes access to its resources
 * depending on the responses of another object, implementing
 * PolicyDecisionPointInterface.
 *
 * @see PolicyDecisionPointInterface
 * @package Authorization
 */
interface Nethgui_Authorization_PolicyEnforcementPointInterface
{

    /**
     * @return PolicyDecisionPointInterface
     */
    public function getPolicyDecisionPoint();

    /**
     * @param Nethgui_Authorization_PolicyDecisionPointInterface $pdp
     */
    public function setPolicyDecisionPoint(Nethgui_Authorization_PolicyDecisionPointInterface $pdp);
}

