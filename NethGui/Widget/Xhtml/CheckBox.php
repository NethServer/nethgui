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
class NethGui_Widget_Xhtml_CheckBox extends NethGui_Widget_Xhtml
{



    public function render()
    {
        $name = $this->getAttribute('name');
        $value = $this->getAttribute('value');
        $flags = $this->getAttribute('flags');
        $content = '';

        $attributes = array(
            'type' => 'checkbox',
            'value' => strval($value),
        );

        if ($value == $this->view[$name]) {
            $flags |= NethGui_Renderer_Abstract::STATE_CHECKED;
        }

        $flags = $this->applyDefaultLabelAlignment($flags, NethGui_Renderer_Abstract::LABEL_RIGHT);


        $hidden = new NethGui_Widget_Xhtml_Hidden($this->view);
        $hidden
            ->setAttribute('name', $name)
            ->setAttribute('flags', $flags)
        ;

        $content .= $hidden->render();
        $content .= $this->labeledControlTag('input', $name, $name, $flags, '', $attributes);

        return $content;
    }

}