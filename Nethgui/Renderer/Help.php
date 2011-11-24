<?php
/**
 */

namespace Nethgui\Renderer;

/**
 *  @author Davide Principi <davide.principi@nethesis.it>
 */
class Help extends Xhtml
{

    public $nestingLevel = 1;
    private $describeWidgets = array();

    protected function createWidget($widgetName, $attributes = array())
    {
        $o = new \Nethgui\Widget\Help($this);

        foreach ($attributes as $aname => $avalue) {
            $o->setAttribute($aname, $avalue);
        }

        $o->setAttribute('do', $widgetName);

        if (in_array($widgetName, array('textInput', 'button', 'checkBox', 'radioButton', 'fieldsetSwitch'))) {
            $this->describeWidgets[] = $o;
        }

        return $o;
    }

    protected function render()
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

            $id = $this->view->getUniqueId($name);

            $fields[] = array(
                'name' => $name,
                'id' => $id,
                'helpId' => $widget->getAttribute('helpId', $name),
                'label' => $label,
            );
        }

        $view = $this->view->spawnView($this->view->getModule());
        $view->setTemplate('Nethgui\Template\Help\Section');
        $view['title'] = $this->getModule()->getTitle();
        $view['description'] = $this->getModule()->getDescription();
        $view['fields'] = $fields;
        $view['titleLevel'] = $this->nestingLevel;
        $headingRenderer = new Xhtml($view);

        return (String) $headingRenderer . $content;
    }

}
