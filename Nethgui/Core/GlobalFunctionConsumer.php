<?php
/**
 * @package Core
 * @author Davide Principi <davide.principi@nethesis.it>
 */

namespace Nethgui\Core;

/**
 * @package Core
 */
interface GlobalFunctionConsumer
{
    public function setGlobalFunctionWrapper(GlobalFunctionWrapper $object);
}
