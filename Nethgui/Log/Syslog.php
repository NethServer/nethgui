<?php
/**
 * @package Log
 * @author Davide Principi <davide.principi@nethesis.it>
 */

namespace Nethgui\Log;

/**
 * @package Log
 */
class Syslog extends AbstractLog
{
    public function message($level, $message)
    {
        $this->globalFunctionWrapper->error_log(sprintf('[%s] %s', strtoupper($level), $message));
        return $this;
    }
}
