<?php
interface AccessControlResponseInterface {
    /**
     * @return AccessControlRequestInterface
     */
    public function getRequest();

    /**
     * @return bool
     */
    public function isAccessGranted();

    /**
     * @return string
     */
    public function getMessage();
}
