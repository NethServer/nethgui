<?php

/**
 * Nethgui
 *
 * @package Authorization
 */

/**
 * Nethgui_Authorization_PolicyDecisionPointInterface (PDP)
 *
 * A PDP implementation is responsible for authorizing access requests coming
 * from PEPs (PolicyEnforcementPointInterfaces).
 *
 * @see PolicyEnforcementPointInterface
 * @package Authorization
 */
interface Nethgui_Authorization_PolicyDecisionPointInterface
{

    /**
     * Checks if $request is satisfiable.
     * @return Nethgui_Authorization_AccessControlResponseInterface The response to the request.
     */
    public function authorizeRequest(Nethgui_Authorization_AccessControlRequestInterface $request);
}

