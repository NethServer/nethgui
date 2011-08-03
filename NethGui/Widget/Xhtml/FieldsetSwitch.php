<?php
/**
 * @package Widget
 * @subpackage Xhtml
 * @author Davide Principi <davide.principi@nethesis.it>
 * @internal
 */

/**
 *
 * @package Widget
 * @subpackage Xhtml
 * @internal
 */
class NethGui_Widget_Xhtml_FieldsetSwitch extends NethGui_Widget_Xhtml
{

    public function render()
    {
        $name = $this->getAttribute('name');
        $value = $this->getAttribute('value');
        $flags = $this->getAttribute('flags');
        $content = '';

        $content .= $this->openTag('div', array('class' => 'fieldset-switch'));

        $radioButton = new NethGui_Widget_Xhtml_RadioButton($this->view);
        $radioButton
            ->setAttribute('name', $name)
            ->setAttribute('value', $value)
            ->setAttribute('flags', $flags)
        ;

        $content .= $radioButton->render();

        $attributes = array(
            'id' => $this->view->getUniqueId(array($name, $value, 'fieldset'))
        );

        
        $content .= $this->renderChildren();
        
        $content .= $this->closeTag('div');
        
        return $content;
    }

    public function insert(NethGui_Renderer_WidgetInterface $widget)
    {
        $fieldset = new NethGui_Widget_Xhtml_Fieldset($this->view);


        parent::insert($fieldset->insert($widget));

        return $this;
    }

}