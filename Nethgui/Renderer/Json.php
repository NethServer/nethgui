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
class Nethgui_Renderer_Json extends Nethgui_Renderer_Abstract
{

    /**
     * Get the array of events to properly transfer the view on the client side.
     * @return array
     */
    private function getClientEvents()
    {
        $events = array();
        $commands = array();
        $this->fillEvents($events, $commands);

        if (count($commands) > 0) {
            $events[] = array('ClientCommandHandler', $commands);
        }
        return $events;
    }

    private function fillEvents(&$events, &$commands)
    {
        foreach ($this as $offset => $value) {

            $eventTarget = $this->getClientEventTarget($offset);
            if ($value instanceof Nethgui_Core_ViewInterface) {
                if ( ! $value instanceof Nethgui_Renderer_Json) {
                    $value = new Nethgui_Renderer_Json($value);
                }
                $value->fillEvents($events, $commands);
                continue;
            } elseif ($value instanceof Nethgui_Client_CommandInterface) {
                $value->setReceiver($eventTarget);
                $commands[] = array(
                    'r' => (String) $value->getReceiver(),
                    'm' => (String) $value->getMethod(),
                    'a' => $value->getArguments()
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
        return json_encode($this->getClientEvents());
    }

}