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
                $commands[] = $value->setReceiver(new Nethgui_Renderer_JsonReceiver($this->view, $offset))->execute();
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

}

/**
 * @ignore
 */
class Nethgui_Renderer_JsonReceiver implements Nethgui_Core_CommandReceiverInterface
{

    private $offset;

    /**
     *
     * @var Nethgui_Core_ViewInterface
     */
    private $view;

    public function __construct(Nethgui_Core_ViewInterface $view, $offset)
    {
        $this->view = $view;
        $this->offset = $offset;
    }

    public function executeCommand($name, $arguments)
    {
        if ($name == 'delay'
            && $arguments[0] instanceof Nethgui_Core_CommandInterface) {
            $receiver = '';
            // replace the first argument with the array equivalent
            $arguments[0] = $arguments[0]->setReceiver(clone $this)->execute();
        } elseif ($name == 'redirect' || $name == 'queryUrl') {
            $receiver = '';
            $arguments[0] = $this->view->getModuleUrl($arguments[0]);
        } elseif ($name == 'activateAction') {
            $receiver = '';

            $tmp = array(
                $this->view->getUniqueId($arguments[0]),
                $this->view->getModuleUrl($arguments[0]),
                $this->view->getUniqueId()
            );

            if (isset($arguments[1])) {
                $tmp[1] = $this->view->getModuleUrl($arguments[1]);
            }

            if (isset($arguments[2])) {
                $tmp[2] = $this->view->getUniqueId($arguments[2]);
            }

            $arguments = $tmp;
        } elseif ($name == 'debug' || $name == 'alert') {
            $receiver = '';
        } else {
            $receiver = is_numeric($this->offset) ? '#' . $this->view->getUniqueId() : '.' . $this->view->getClientEventTarget($this->offset);
        }

        return $this->commandForClient($receiver, $name, $arguments);
    }

    private function commandForClient($receiver, $name, $arguments)
    {
        return array(
            'receiver' => $receiver,
            'methodName' => $name,
            'arguments' => $arguments,
        );
    }

}