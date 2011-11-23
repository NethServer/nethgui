<?php
/**
 * @package Log
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * @package Log
 */
class Nethgui\Log\Syslog extends Nethgui\Log\AbstractLog
{
    public function message($level, $message)
    {
        $this->globalFunctionWrapper->error_log(sprintf('[%s] %s', strtoupper($level), $message));
        return $this;
    }
}