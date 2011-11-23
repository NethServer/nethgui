<?php

/**
 * Nethgui
 *
 * @package Authorization
 */

/**
 * Nethgui\Authorization\PolicyDecisionPointInterface (PDP)
 *
 * A PDP implementation is responsible for authorizing access requests coming
 * from PEPs (PolicyEnforcementPointInterfaces).
 *
 * @see PolicyEnforcementPointInterface
 * @package Authorization
 */
interface Nethgui\Authorization\PolicyDecisionPointInterface
{

    /**
     * Checks if $request is satisfiable.
     * @return Nethgui\Authorization\AccessControlResponseInterface The response to the request.
     */
    public function authorizeRequest(Nethgui\Authorization\AccessControlRequestInterface $request);
}

