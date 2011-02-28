<?php
/**
 * NethGui
 *
 * @package Authorization
 */

/**
 * TODO: describe interface
 *
 * @package Authorization
 */
interface PolicyDecisionPointInterface {
    /**
     * @return AccessControlResponseInterface
     */
    public function authorizeRequest(AccessControlRequestInterface $request);
}


