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
     * Saves aggregated values into database,
     * forwarding the call to Adapters and Sets..
     * 
     * @return integer The number of saved parameters. A zero value indicates that nothing has been saved.
     */
    public function save();
}
