<?php
/**
 * @package Log
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * @package Log
 */
interface Nethgui_Log_LogConsumerInterface
{
    public function setLog(Nethgui_Log_AbstractLog $log);

    /**
     * @return Nethgui_Log_AbstractLog
     */
    public function getLog();
}
