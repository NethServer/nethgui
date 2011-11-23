<?php

/**
 * Nethgui
 *
 * @package Authorization
 */

namespace Nethgui\Authorization;

/**
 * PolicyDecisionPointInterface (PDP)
 *
 * A PDP implementation is responsible for authorizing access requests coming
 * from PEPs (PolicyEnforcementPointInterfaces).
 *
 * @see PolicyEnforcementPointInterface
 * @package Authorization
 */
interface PolicyDecisionPointInterface
{

    /**
     * Checks if $request is satisfiable.
     * @return AccessControlResponseInterface The response to the request.
     */
    public function authorizeRequest(AccessControlRequestInterface $request);
}

