<?php

/**
 * NethGui
 *
 * @package NethGui
 * @subpackage Authorization
 */

/**
 * NethGui_Authorization_PolicyDecisionPointInterface (PDP)
 *
 * A PDP implementation is responsible for authorizing access requests coming
 * from PEPs (PolicyEnforcementPointInterfaces).
 *
 * @see PolicyEnforcementPointInterface
 * @package NethGui
 * @subpackage Authorization
 */
interface NethGui_Authorization_PolicyDecisionPointInterface
{

    /**
     * Checks if $request is satisfiable.
     * @return NethGui_Authorization_AccessControlResponseInterface The response to the request.
     */
    public function authorizeRequest(NethGui_Authorization_AccessControlRequestInterface $request);
}

