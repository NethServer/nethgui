<?php
/**
 * @package Log
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * @package Log
 */
abstract class Nethgui\Log\AbstractLog implements Nethgui\Core\GlobalFunctionConsumer
{

    /**
     * @var Nethgui\Core\GlobalFunctionWrapper
     */
    protected $globalFunctionWrapper;

    public function __construct()
    {
        $this->globalFunctionWrapper = new Nethgui\Core\GlobalFunctionWrapper();
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

    public function setGlobalFunctionWrapper(Nethgui\Core\GlobalFunctionWrapper $object)
    {
        $this->globalFunctionWrapper = $object;
        return $this;
    }

}