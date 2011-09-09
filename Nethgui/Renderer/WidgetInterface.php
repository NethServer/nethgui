<?php
/**
 * @package Renderer
 * @author Davide Principi <davide.principi@nethesis.it>
 */

interface Nethgui_Renderer_WidgetInterface {
    public function insert(Nethgui_Renderer_WidgetInterface $widget);
    public function setAttribute($attribute, $value);
    public function getAttribute($attribute, $default = NULL);
    public function render();
}
