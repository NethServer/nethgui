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
        return $this->fillEvents($events);
    }

    private function fillEvents(&$events)
    {
        foreach ($this as $offset => $value) {

            $eventTarget = $this->getClientEventTarget($offset);
            if ($value instanceof Nethgui_Core_ViewInterface) {
                if ( ! $value instanceof Nethgui_Renderer_Json) {
                    $value = new Nethgui_Renderer_Json($value);
                }
                $value->fillEvents($events);
                continue;
            } elseif ($value instanceof Traversable) {
                $eventData = $this->traversableToArray($value);
            } else {
                $eventData = $value;
            }

            $events[] = array($eventTarget, $eventData);
        }

        return $events;
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