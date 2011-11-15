<?php
/**
 * @package Log
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * @package Log
 */
class Nethgui_Log_Nullog extends Nethgui_Log_AbstractLog
{

    public function message($level, $message)
    {
        return $this;
    }

}