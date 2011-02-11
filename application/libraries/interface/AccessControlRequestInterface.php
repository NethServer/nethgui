<?php

interface AccessControlRequestInterface {

    /**
     * @return AccessControlAttributeAggregationInterface
     */
    public function getSubject();

    /**
     * @return string
     */
    public function getResource();

    /**
     * @return string
     */
    public function getAction();
}

