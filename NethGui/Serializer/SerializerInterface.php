<?php
/**
 * @package Serializer
 */

/**
 * A Serializer object transfers data to/from a database Key or Prop.
 *
 * @package Serializer
 */
interface NethGui_Serializer_SerializerInterface {
    public function read();
    public function write($value);
}
