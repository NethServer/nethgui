<?php
/**
 * @package Log
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * @package Log
 */
interface Nethgui\Log\LogConsumerInterface
{
    public function setLog(Nethgui\Log\AbstractLog $log);

    /**
     * @return Nethgui\Log\AbstractLog
     */
    public function getLog();
}
