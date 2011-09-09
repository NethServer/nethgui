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
class Nethgui_Authorization_PermissivePolicyDecisionPoint implements Nethgui_Authorization_PolicyDecisionPointInterface
{

    public function authorizeRequest(Nethgui_Authorization_AccessControlRequestInterface $request)
    {
        // TODO: log a debug message
        return new Nethgui_Authorization_AccessControlResponse($request);
    }

}

