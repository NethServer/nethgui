<?php
/**
 * @package Renderer
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * Transform a view into a string.
 *
 * @see Nethgui_Renderer_WidgetInterface
 * @see http://en.wikipedia.org/wiki/Decorator_pattern
 * @package Renderer
 */
abstract class Nethgui_Renderer_Abstract extends Nethgui_Core_ReadonlyView
{
    abstract protected function render();

    public function __toString()
    {
        try {
            return $this->render();
        } catch (Exception $ex) {
            error_log($ex->getMessage() . '; ' . sprintf('file: %s, line: %d.', $ex->getFile(), $ex->getLine()));
            throw $ex;
        }
    }

}

