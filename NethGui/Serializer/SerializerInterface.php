<?php
/**
 * @package Serializer
 */

/**
 * A Serializer object transfers a value to/from the configuration database.
 *
 * @package Serializer
 */
interface NethGui_Serializer_SerializerInterface {
    public function read();
    public function write($value);
}
