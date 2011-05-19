<?php
/**
 * @package Adapter
 */

/**
 * @package Adapter
 */
interface NethGui_Adapter_AdapterAggregationInterface {

    /**
     * @param NethGui_Adapter_AdapterInterface $adapter
     * @param string $key
     */
    public function register(NethGui_Adapter_AdapterInterface $adapter, $key);
    
    /**
     * Check if a member is modified from its initial value.
     * 
     * If the member to check is not specified (NULL) the method checks if any
     * of its member is modified and returns TRUE on this case.
     * 
     * @param string $key Optional The member to check. 
     * @return bool
     */
    public function isModified($key = NULL);
    
    /**
     * Saves aggregated values into database,
     * forwarding the call to Adapters and Sets..
     * 
     * @return integer The number of saved parameters. A zero value indicates that nothing has been saved.
     */
    public function save();
}
