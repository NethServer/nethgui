<?php
/**
 * @package Renderer
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * @package Renderer
 */
class Nethgui_Renderer_Xhtml extends Nethgui_Core_ReadonlyView implements Nethgui_Renderer_Abstract
{

    /**
     *
     * @var integer
     */
    private $inheritFlags = 0;

    /**
     *
     * @param Nethgui_Core_ViewInterface $view
     * @param int $inheritFlags Default flags applied to all widgets created by this renderer
     */
    public function __construct(Nethgui_Core_ViewInterface $view, $inheritFlags = 0)
    {
        parent::__construct($view);

        $inheritableFlagsMask = self::STATE_DISABLED
            | self::LABEL_ABOVE
            | self::LABEL_LEFT
            | self::LABEL_RIGHT
        ;

        $this->inheritFlags = $inheritFlags & $inheritableFlagsMask;
    }

    private function createWidget($widgetName, $attributes = array())
    {
        $className = 'Nethgui_Widget_Xhtml_' . ucfirst($widgetName);

        $o = new $className($this);

        foreach ($attributes as $aname => $avalue) {
            $o->setAttribute($aname, $avalue);
        }

        return $o;
    }

    public function elementList($flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('flags' => $flags));
    }

    public function button($name, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'flags' => $flags));
    }

    public function checkBox($name, $value, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'value' => $value, 'flags' => $flags));
    }

    public function dialog($name, $flags = 0)
    {
        $flags |= $this->inheritFlags;

        $className = 'dialog';

        if ($flags & Nethgui_Renderer_Abstract::DIALOG_SUCCESS) {
            $className .= ' success';
        } elseif ($flags & Nethgui_Renderer_Abstract::DIALOG_WARNING) {
            $className .= ' warning';
        } elseif ($flags & Nethgui_Renderer_Abstract::DIALOG_ERROR) {
            $className .= ' error';
        }

        if ($flags & Nethgui_Renderer_Abstract::DIALOG_MODAL) {
            $className .= ' modal';
        }

        if ($flags & Nethgui_Renderer_Abstract::STATE_DISABLED) {
            $className .= ' disabled';
        }

        /*
         * Create a panel wrapped around the inset
         */

        $panel = $this->panel($flags)
            ->setAttribute('class', $className)
            ->setAttribute('name', $name);
        $inset = $this->createWidget('inset', array('name' => $name, 'flags' => $flags));

        $panel->insert($inset);

        return $panel;
    }

    public function fieldsetSwitch($name, $value, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'value' => $value, 'flags' => $flags));
    }

    public function form($flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('flags' => $flags));
    }

    public function hidden($name, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'flags' => $flags));
    }

    public function inset($name, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'flags' => $flags));
    }

    public function panel($flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('flags' => $flags));
    }

    public function radioButton($name, $value, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'value' => $value, 'flags' => $flags));
    }

    public function selector($name, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'flags' => $flags));
    }

    public function tabs($flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('flags' => $flags));
    }

    public function textInput($name, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'flags' => $flags));
    }

    public function textLabel($name, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'flags' => $flags));
    }

    public function fieldset($name = NULL, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        $widget = $this->createWidget(__FUNCTION__, array('flags' => $flags, 'icon-before' => 'ui-icon-triangle-1-s'));
        if ( ! is_null($name)) {
            $widget->setAttribute('name', $name);
        }
        return $widget;
    }

    public function header($name = NULL, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        $widget = $this->createWidget('textLabel', array('flags' => $flags, 'class' => 'header ui-widget-header ui-corner-all ui-helper-clearfix', 'tag' => 'div'));
        if ( ! is_null($name)) {
            $widget->setAttribute('name', $name);
        }
        return $widget;
    }

    public function literal($data)
    {
        return $this->createWidget(__FUNCTION__, array('data' => $data));
    }

    public function columns()
    {
        return $this->createWidget(__FUNCTION__, array());
    }

}