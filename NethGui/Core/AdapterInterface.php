<?php
/**
 * @package Core
 */

/**
 * Adapter Interface allows changing a ConfigurationDatabase key or property value
 * through a simplified interface.
 * 
 * @see NethGui_Core_AdapterAggregationInterface
 * @package Core
 */
interface NethGui_Core_AdapterInterface
{

    /**
     * @var mixed $value
     * @return void
     */
    public function set($value);

    /**
     * @return mixed
     */
    public function get();

    /**
     * @return void
     */
    public function delete();

    /**
     * @return bool;
     */
    public function isModified();

    /**
     * @return void
     */
    public function save();
}