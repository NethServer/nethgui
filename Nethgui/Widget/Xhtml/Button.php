<?php
/**
 * @package Widget
 * @subpackage Xhtml
 * @author Davide Principi <davide.principi@nethesis.it>
 * @internal
 * @ignore
 */

/**
 *
 * Attributes:
 * - name
 * - value
 * - flags
 * - label
 *
 * @package Widget
 * @subpackage Xhtml
 * @internal
 * @ignore
 */
class Nethgui_Widget_Xhtml_Button extends Nethgui_Widget_Xhtml
{

    public function render()
    {
        $name = $this->getAttribute('name');
        $flags = $this->getAttribute('flags');
        $label = $this->getAttribute('label', $name . '_label');
        $content = '';

        $attributes = array();
        $cssClass = 'Button';

        if ($flags & (Nethgui_Renderer_Abstract::BUTTON_LINK | Nethgui_Renderer_Abstract::BUTTON_CANCEL | Nethgui_Renderer_Abstract::BUTTON_HELP)) {

            $value = $this->getAttribute('value', isset($this->view[$name]) ? $this->view[$name] : NULL);

            if (is_null($value)) {
                if ($flags & Nethgui_Renderer_Abstract::BUTTON_LINK) {
                    $value = $name;
                } elseif ($flags & Nethgui_Renderer_Abstract::BUTTON_CANCEL) {
                    $value = '..';
                }
            }

            if ($flags & Nethgui_Renderer_Abstract::BUTTON_CANCEL) {
                $cssClass .= ' cancel';
            } elseif ($flags & Nethgui_Renderer_Abstract::BUTTON_HELP) {
                // Pick the root module identifier:
                $value = '/Help/Read/' . array_shift($this->view->getModulePath()) . '.html#HelpArea';
                $cssClass .= ' Help';
            } else {
                $cssClass .= ' link';
            }

            $attributes['href'] = $this->prepareHrefAttribute($value);
            $attributes['class'] = $cssClass;
            $attributes['title'] = $this->getAttribute('title', FALSE);

            $content .= $this->openTag('a', $attributes);
            $content .= $this->view->translate($label);
            $content .= $this->closeTag('a');
        } else {
            if ($flags & Nethgui_Renderer_Abstract::BUTTON_SUBMIT) {
                $attributes['type'] = 'submit';
                $cssClass .= ' submit';
                $attributes['id'] = FALSE;
                $attributes['name'] = FALSE;
            } elseif ($flags & Nethgui_Renderer_Abstract::BUTTON_RESET) {
                $attributes['type'] = 'reset';
                $cssClass .= ' reset';
            } elseif ($flags & Nethgui_Renderer_Abstract::BUTTON_CUSTOM) {
                $attributes['type'] = 'button';
                $cssClass .= ' custom';
            } elseif ($flags & Nethgui_Renderer_Abstract::BUTTON_DROPDOWN) {
                $attributes['type'] = 'button';
                $attributes['name'] = FALSE;
                $attributes['id'] = FALSE;
                $cssClass .= ' dropdown';
                $childContent = $this->renderChildren();
            }

            $attributes['value'] = $this->view->translate($label);

            $content .= $this->controlTag('button', $name, $flags, $cssClass, $attributes);
            if (isset($childContent)) {
                $content .= $childContent;
            }
        }

        return $content;
    }

    private function prepareHrefAttribute($value)
    {
        if (is_string($value) && preg_match('/https?/', parse_url($value, PHP_URL_SCHEME))) {
            return $value;
        }

        if ( ! is_array($value)) {
            $value = array($value);
        }

        return call_user_func_array(array($this, 'buildUrl'), $value);
    }

}

