<?php
/**
 * @package Renderer
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * @package Renderer
 */
class NethGui_Renderer_Xhtml extends NethGui_Renderer_Abstract
{

    private function createWidget($widgetName, $attributes = array())
    {
        $className = 'NethGui_Widget_Xhtml_' . ucfirst($widgetName);

        $o = new ${className}($this);

        foreach ($attributes as $aname => $avalue) {
            $o->setAttribute($name, $value);
        }

        return $o;
    }

    public function elementList($flags = 0)
    {
        return $this->createWidget(__FUNCTION__, array('flags' => $flags));
    }

    public function button($name, $flags = 0)
    {
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'flags' => $flags));
    }

    public function checkBox($name, $value, $flags = 0)
    {
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'value' => $value, 'flags' => $flags));
    }

    public function dialog($flags = 0)
    {
        return $this->createWidget(__FUNCTION__, array('flags' => $flags));
    }

    public function fieldsetSwitch($name, $value, $flags = 0)
    {
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'value' => $value, 'flags' => $flags));
    }

    public function form($flags = 0)
    {
        return $this->createWidget(__FUNCTION__, array('flags' => $flags));
    }

    public function hidden($name, $flags = 0)
    {
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'flags' => $flags));
    }

    public function inset($name, $flags = 0)
    {
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'flags' => $flags));
    }

    public function panel($flags = 0)
    {
        return $this->createWidget(__FUNCTION__, array('flags' => $flags));
    }

    public function radioButton($name, $value, $flags = 0)
    {
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'value' => $value, 'flags' => $flags));
    }

    public function selector($name, $flags = 0)
    {
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'flags' => $flags));
    }

    public function tabs($flags = 0)
    {
        return $this->createWidget(__FUNCTION__, array('flags' => $flags));
    }

    public function textInput($name, $flags = 0)
    {
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'flags' => $flags));
    }

    public function text($name, $flags = 0)
    {
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'flags' => $flags));
    }

}
