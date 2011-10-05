<?php
/**
 * @package Renderer
 */

/**
 *  @author Davide Principi <davide.principi@nethesis.it>
 * @package Renderer
 */
class Nethgui_Renderer_Help extends Nethgui_Renderer_Xhtml
{
    protected $nestingLevel = 1;

    public function setNestingLevel($level)
    {
        $this->nestingLevel = $level;
        return $this;
    }

    protected function createWidget($widgetName, $attributes = array())
    {
        $className = 'Nethgui_Widget_Help';

        $o = new $className($this);

        foreach ($attributes as $aname => $avalue) {
            $o->setAttribute($aname, $avalue);
        }

        $o->setAttribute('do', $widgetName);

        return $o;
    }

    public function inset($name, $flags = 0)
    {
        $widget = parent::inset($name, $flags);
        $widget->setAttribute('titleLevel', $this->nestingLevel);
        return $widget;
    }

}