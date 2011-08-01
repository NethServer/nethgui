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
        $name = $this->getParameter('name');
        $value = $this->getParameter('value');
        $flags = $this->getParameter('flags');
        $content ='';

        $attributes = array();
        $cssClass = 'button';
        $buttonLabel = $name . '_label';
        
        if ($flags & (self::BUTTON_LINK | self::BUTTON_CANCEL)) {

            if (is_null($value)) {
                if ($flags & self::BUTTON_LINK) {
                    $value = $name;
                } else {
                    $value = '..';
                }
            }

            if ($flags & self::BUTTON_CANCEL) {
                $cssClass .= ' cancel';
            } else {
                $cssClass .= ' link';
            }

            if ( ! is_array($value)) {
                $value = array($value);
            }

            $url = call_user_func_array(array($this, 'buildUrl'), $value);
            $attributes['href'] = $url;
            $attributes['class'] = $cssClass;

            $content .= $this->openTag('a', $attributes);
            $content .= $this->translate($buttonLabel);
            $content .= $this->closeTag('a');
        } else {

            if ($flags & self::BUTTON_SUBMIT) {
                $attributes['type'] = 'submit';
                $cssClass .= ' submit';
                $attributes['id'] = FALSE;
                $attributes['name'] = FALSE;
            } elseif ($flags & self::BUTTON_RESET) {
                $attributes['type'] = 'reset';
                $cssClass .= ' reset';
            } elseif ($flags & self::BUTTON_CUSTOM) {
                $attributes['type'] = 'button';
                $cssClass .= ' custom';
            }

            $attributes['value'] = $this->translate($buttonLabel);

            $content .= $this->controlTag('button', $name, $flags, $cssClass, $attributes);
        }

        return $content;
    }

}