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

        $content .= $this->openTag('div', array('class' => 'FieldsetSwitch'));

        $chooser = new NethGui_Widget_Xhtml_RadioButton($this->view);
        $chooser
            ->setAttribute('name', $name)
            ->setAttribute('value', $value)
            ->setAttribute('flags', $flags)
        ;

        $content .= $chooser->render();
        $content .= $this->openTag('fieldset');
        $content .= $this->renderChildren();        
        $content .= $this->closeTag('fieldset');
        $content .= $this->closeTag('div');
        
        return $content;
    }

}