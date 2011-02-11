<?php
interface PolicyDecisionPointInterface {
    public function authorizeRequest(AccessControlRequestInterface $request);
}

