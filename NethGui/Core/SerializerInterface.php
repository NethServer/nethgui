<?php
/**
 * @package NethGui
 * @subpackage Core
 */

/**
 * @package NethGui
 * @subpackage Core
 */
interface NethGui_Core_SerializerInterface {
    public function read();
    public function write($value);
}
