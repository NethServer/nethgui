<?php
/**
 * @package Core
 */

/**
 * @package Core
 */
interface NethGui_Core_AdapterAggregationInterface {

    /**
     * @param NethGui_Core_AdapterInterface $adapter
     * @param string $key
     */
    public function register(NethGui_Core_AdapterInterface $adapter, $key);
    
    /**
     * Saves aggregated values into database,
     * forwarding the call to Adapters and Sets..
     */
    public function save();
}
