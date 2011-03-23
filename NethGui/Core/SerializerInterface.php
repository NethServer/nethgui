<?php

interface NethGui_Core_SerializerInterface {
    public function read();
    public function write($value);
}
