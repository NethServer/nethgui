<?php
/**
 * @package Renderer
 * @author Davide Principi <davide.principi@nethesis.it>
 */

interface NethGui_Renderer_WidgetInterface {
    public function insert(NethGui_Renderer_WidgetInterface $widget);
    public function setAttribute($name, $value);
    public function getAttribute($name);
    public function render();
}
