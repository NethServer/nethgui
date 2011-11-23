<?php
/**
 * @package Log
 * @author Davide Principi <davide.principi@nethesis.it>
 */

namespace Nethgui\Log;

/**
 * @package Log
 */
interface LogConsumerInterface
{
    public function setLog(AbstractLog $log);

    /**
     * @return AbstractLog
     */
    public function getLog();
}
