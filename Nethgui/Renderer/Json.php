<?php
/**
 * @package Renderer
 * @ignore
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * @package Renderer
 * @ignore
 */
class Nethgui_Renderer_Json extends Nethgui_Renderer_Abstract implements Nethgui_Core_CommandReceiverInterface
{

    private function deepWalk(&$events, &$commands)
    {
        foreach ($this as $offset => $value) {

            $eventTarget = $this->getClientEventTarget($offset);
            if ($value instanceof Nethgui_Core_ViewInterface) {
                if ( ! $value instanceof Nethgui_Renderer_Json) {
                    $value = new Nethgui_Renderer_Json($value);
                }
                $value->deepWalk($events, $commands);
                continue;
            } elseif ($value instanceof Nethgui_Core_CommandInterface) {

                $executedCommand = $value->setReceiver($this)->execute();

                $commands[] = array(
                    'receiver' => is_numeric($offset) ? '#' . $this->getUniqueId() : '.' . $eventTarget,
                    'methodName' => $executedCommand[0],
                    'arguments' => $executedCommand[1]
                );
                
                continue;
            } elseif ($value instanceof Traversable) {
                $eventData = $this->traversableToArray($value);
            } else {
                $eventData = $value;
            }

            $events[] = array($eventTarget, $eventData);
        }
    }

    /**
     * Convert a Traversable object to a PHP array
     * @param Traversable $value
     * @return array
     */
    private function traversableToArray(Traversable $value)
    {
        $a = array();
        foreach ($value as $k => $v) {
            if ($v instanceof Traversable) {
                $v = $this->traversableToArray($v);
            }
            $a[$k] = $v;
        }
        return $a;
    }

    protected function render()
    {
        $events = array();
        $commands = array();

        $this->deepWalk($events, $commands);
        if (count($commands) > 0) {
            $events[] = array('ClientCommandHandler', $commands);
        }

        return json_encode($events);
    }

    public function executeCommand($name, $arguments)
    {
        return array($name, $arguments);
    }

}