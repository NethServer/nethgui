<?php
/**
 * NethGui
 *
 * @package Authorization
 */

/**
 * TODO: describe class
 *
 * @package Authorization
 */
class PermissivePolicyDecisionPoint implements PolicyDecisionPointInterface {

    public function authorizeRequest(AccessControlRequestInterface $request)
    {
        // TODO: log a debug message
        return new AccessControlResponse($request);
    }

}
