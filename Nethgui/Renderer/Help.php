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

    public $nestingLevel = 1;
    private $describeWidgets = array();

    public function __construct(Nethgui_Core_ViewInterface $view, $inheritFlags = 0)
    {
        if ($view instanceof Nethgui_Renderer_Abstract) {
            // Prevent re-wrapping of a Renderer instance:
            parent::__construct($view->getInnerView(), $inheritFlags);
        } else {
            parent::__construct($view, $inheritFlags);
        }
    }

    protected function createWidget($widgetName, $attributes = array())
    {
        $o = new Nethgui_Widget_Help($this);

        foreach ($attributes as $aname => $avalue) {
            $o->setAttribute($aname, $avalue);
        }

        $o->setAttribute('do', $widgetName);

        if (in_array($widgetName, array('textInput', 'button', 'checkBox', 'radioButton', 'fieldsetSwitch'))) {
            $this->describeWidgets[] = $o;
        }

        return $o;
    }

    public function __toString()
    {
        $fields = array();

        $content = parent::__toString();

        foreach ($this->describeWidgets as $widget) {
            if ( ! $widget->hasAttribute('do')) {
                continue;
            }

            $name = $widget->getAttribute('name');
            $value = $widget->getAttribute('value');
            $whatToDo = $widget->getAttribute('do');

            if (in_array($whatToDo, array('textInput', 'button'))) {
                $label = $widget->getAttribute('label', $name . '_label');
            } elseif (in_array($whatToDo, array('checkBox', 'radioButton', 'fieldsetSwitch'))) {
                $label = $widget->getAttribute('label', $name . '_' . $value . '_label');
            }

            $id = $this->getInnerView()->getUniqueId($name);

            $fields[] = array(
                'name' => $name,
                'id' => $id,
                'helpId' => $widget->getAttribute('helpId', $name),
                'label' => $label,
            );
        }

        $view = $this->getInnerView()->spawnView($this->getInnerView()->getModule());
        $view->setTemplate('Nethgui_Template_Help_Section');
        $view['title'] = $this->getModule()->getTitle();
        $view['description'] = $this->getModule()->getDescription();
        $view['fields'] = $fields;
        $view['titleLevel'] = $this->nestingLevel;
        $headingRenderer = new Nethgui_Renderer_Xhtml($view);

        return (String) $headingRenderer . $content;
    }

}