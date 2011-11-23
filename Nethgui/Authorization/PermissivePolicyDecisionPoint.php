<?php

/**
 * Nethgui
 *
 * @package Authorization
 */

/**
 * TODO: describe class
 *
 * @package Authorization
 */
class Nethgui\Authorization\PermissivePolicyDecisionPoint implements Nethgui\Authorization\PolicyDecisionPointInterface
{

    public function authorizeRequest(Nethgui\Authorization\AccessControlRequestInterface $request)
    {
        // TODO: log a debug message
        return new Nethgui\Authorization\AccessControlResponse($request);
    }

}

