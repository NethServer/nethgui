<?php
/**
 * @author Davide Principi <davide.principi@nethesis.it>
 */

namespace Nethgui\Log;

/**
 */
interface LogConsumerInterface
{
    public function setLog(AbstractLog $log);

    /**
     * @return AbstractLog
     */
    public function getLog();
}
