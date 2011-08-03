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
class NethGui_Widget_Xhtml_Button extends NethGui_Widget_Xhtml
{

    public function render()
    {
        $name = $this->getAttribute('name');
        $value = $this->getAttribute('value');
        $flags = $this->getAttribute('flags');
        $content ='';

        $attributes = array();
        $cssClass = 'button';
        $buttonLabel = $name . '_label';
        
        if ($flags & (NethGui_Renderer_Abstract::BUTTON_LINK | NethGui_Renderer_Abstract::BUTTON_CANCEL)) {

            if (is_null($value)) {
                if ($flags & NethGui_Renderer_Abstract::BUTTON_LINK) {
                    $value = $name;
                } else {
                    $value = '..';
                }
            }

            if ($flags & NethGui_Renderer_Abstract::BUTTON_CANCEL) {
                $cssClass .= ' cancel';
            } else {
                $cssClass .= ' link';
            }

            if ( ! is_array($value)) {
                $value = array($value);
            }
            
            $attributes['href'] = call_user_func_array(array($this, 'buildUrl'), $value);
            $attributes['class'] = $cssClass;

            $content .= $this->openTag('a', $attributes);
            $content .= $this->view->translate($buttonLabel);
            $content .= $this->closeTag('a');
        } else {

            if ($flags & NethGui_Renderer_Abstract::BUTTON_SUBMIT) {
                $attributes['type'] = 'submit';
                $cssClass .= ' submit';
                $attributes['id'] = FALSE;
                $attributes['name'] = FALSE;
            } elseif ($flags & NethGui_Renderer_Abstract::BUTTON_RESET) {
                $attributes['type'] = 'reset';
                $cssClass .= ' reset';
            } elseif ($flags & NethGui_Renderer_Abstract::BUTTON_CUSTOM) {
                $attributes['type'] = 'button';
                $cssClass .= ' custom';
            }

            $attributes['value'] = $this->view->translate($buttonLabel);

            $content .= $this->controlTag('button', $name, $flags, $cssClass, $attributes);
        }

        return $content;
    }

}

