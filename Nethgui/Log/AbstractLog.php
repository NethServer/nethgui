<?php
/**
 * @package Log
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * @package Log
 */
abstract class Nethgui_Log_AbstractLog implements Nethgui_Core_GlobalFunctionConsumer
{

    /**
     * @var Nethgui_Core_GlobalFunctionWrapper
     */
    protected $globalFunctionWrapper;

    public function __construct()
    {
        $this->globalFunctionWrapper = new Nethgui_Core_GlobalFunctionWrapper();
    }

    public function exception(Exception $ex, $stackTrace = FALSE)
    {
        $message = sprintf('%s : file %s; line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine());
        $retval = $this->message(__FUNCTION__, $message);

        if ($stackTrace) {
            foreach (explode("\n", $ex->getTraceAsString()) as $line) {
                $this->message(__FUNCTION__, $line);
            }
        }

        return $retval;
    }

    public function debug($message)
    {
        return $this->message(__FUNCTION__, $message);
    }

    public function notice($message)
    {
        return $this->message(__FUNCTION__, $message);
    }

    public function info($message)
    {
        return $this->notice($message);
    }

    public function error($message)
    {
        return $this->message(__FUNCTION__, $message);
    }

    public function warning($message)
    {
        return $this->message(__FUNCTION__, $message);
    }

    abstract public function message($level, $message);

    public function setGlobalFunctionWrapper(Nethgui_Core_GlobalFunctionWrapper $object)
    {
        $this->globalFunctionWrapper = $object;
        return $this;
    }

}