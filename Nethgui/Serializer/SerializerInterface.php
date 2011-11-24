<?php
/**
 */

namespace Nethgui\Serializer;

/**
 * A Serializer object transfers a value to/from the configuration database.
 *
 */
interface SerializerInterface {
    public function read();
    public function write($value);
}
