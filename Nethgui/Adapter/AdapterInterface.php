<?php
/**
 * @package Adapter
 */

/**
 * Adapter Interface allows changing a ConfigurationDatabase key or property value
 * through a simplified interface.
 * 
 * @see Nethgui_Adapter_AdapterAggregationInterface
 * @package Adapter
 */
interface Nethgui_Adapter_AdapterInterface
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
     * The number of values saved
     * @return integer
     */
    public function save();
}