<?php

/**
 * NethGui
 *
 * @package NethGui
 * @subpackage Authorization
 */

/**
 * TODO: describe class
 *
 * @package NethGui
 * @subpackage Authorization
 */
class NethGui_Authorization_PermissivePolicyDecisionPoint implements NethGui_Authorization_PolicyDecisionPointInterface
{

    public function authorizeRequest(NethGui_Authorization_AccessControlRequestInterface $request)
    {
        // TODO: log a debug message
        return new NethGui_Authorization_AccessControlResponse($request);
    }

}

