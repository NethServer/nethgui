<?php
/**
 * @package Widget
 * @author Davide Principi <davide.principi@nethesis.it>
 * @ignore
 */

/**
 * Abstract Xhtml Widget class
 * @ignore
 */
abstract class Nethgui_Widget_Xhtml extends Nethgui_Widget_Abstract
{

    /**
     * Push a LABEL tag
     *
     * @see http://www.w3.org/TR/html401/interact/forms.html#h-17.9.1
     * @param string $name
     * @param string $id
     * @return string
     */
    private function label($text, $id)
    {
        $content = '';
        $content .= $this->openTag('label', array('for' => $id));
        $content .= htmlspecialchars($this->view->translate($text));
        $content .= $this->closeTag('label');
        return $content;
    }

    /**
     *
     * @see controlTag()
     * @param string $tag The XHTML tag of the control.
     * @param string $name The name of the view parameter that holds the data
     * @param string $label The label text
     * @param integer $flags Flags bitmask {STATE_CHECKED, STATE_DISABLED, LABEL_*}
     * @param string $cssClass The `class` attribute value
     * @param array $attributes  Generic attributes array See {@link openTag()}
     * @return string
     */
    protected function labeledControlTag($label, $tag, $name, $flags, $cssClass = '', $attributes = array(), $tagContent = '')
    {
        if (isset($attributes['id'])) {
            $controlId = $attributes['id'];
        } else {
            $controlId = $this->view->getUniqueId($name);
            $attributes['id'] = $controlId;
        }

        $wrapperClass = 'labeled-control';
        $content = '';

        if ($flags & Nethgui_Renderer_Abstract::LABEL_NONE) {
            $content .= $this->controlTag($tag, $name, $flags, $cssClass, $attributes, $tagContent);
        } else {

            if ($flags & Nethgui_Renderer_Abstract::LABEL_RIGHT) {
                $wrapperClass .= ' label-right';
                $content .= $this->openTag('div', array('class' => $wrapperClass));
                $content .= $this->controlTag($tag, $name, $flags, $cssClass, $attributes, $tagContent);
                $content .= $this->label($label, $controlId);
                $content .= $this->closeTag('div');
            } else {
                if ($flags & Nethgui_Renderer_Abstract::LABEL_ABOVE) {
                    $wrapperClass .= ' label-above';
                } elseif ($flags & Nethgui_Renderer_Abstract::LABEL_LEFT) {
                    $wrapperClass .= ' label-left';
                }
                $content .= $this->openTag('div', array('class' => $wrapperClass));
                $content .= $this->label($label, $controlId);
                $content .= $this->controlTag($tag, $name, $flags, $cssClass, $attributes, $tagContent);
                $content .= $this->closeTag('div');
            }
        }

        return $content;
    }

    /**
     * Push an HTML tag for parameter $name.
     *
     * @param string $tag The XHTML tag of the control.
     * @param string|array $name The name of the view parameter that holds the data
     * @param integer $flags Flags bitmask {STATE_CHECKED, STATE_DISABLED}
     * @param string $cssClass The `class` attribute value
     * @param array $attributes Generic attributes array See {@link openTag()}
     * @return string
     */
    protected function controlTag($tag, $name, $flags, $cssClass = '', $attributes = array(), $tagContent = '')
    {
        // Add default instance flags:
        $flags |= intval($this->getAttribute('flags'));
        $tag = strtolower($tag);

        if ( ! isset($attributes['id'])) {
            $attributes['id'] = $this->view->getUniqueId($name);
        }

        if ( ! isset($attributes['name'])) {
            $attributes['name'] = $this->view->getControlName($name);
        }

        $isCheckable = ($tag == 'input') && isset($attributes['type']) && ($attributes['type'] == 'checkbox' || $attributes['type'] == 'radio');

        if ($flags & Nethgui_Renderer_Abstract::STATE_CHECKED && $isCheckable) {
            $attributes['checked'] = 'checked';
        }
        if ($flags & Nethgui_Renderer_Abstract::STATE_READONLY) {
            if ($isCheckable) {
                // `readonly` attribute is not appliable to checkable controls
                $attributes['disabled'] = 'disabled';
            } else {
                $attributes['readonly'] = 'readonly';
            }
        }

        if ($tag == 'button') {
            $tagContent = $attributes['value'];
        }

        if (in_array($tag, array('input', 'button', 'textarea', 'select', 'optgroup', 'option'))) {
            if ($flags & Nethgui_Renderer_Abstract::STATE_DISABLED) {
                $attributes['disabled'] = 'disabled';
            }

            if ($flags & Nethgui_Renderer_Abstract::STATE_VALIDATION_ERROR) {
                $cssClass .= ' validation-error ui-state-error';
            }
        }

        if ( ! isset($attributes['class'])) {
            $attributes['class'] = trim($cssClass . ' ' . $this->getClientEventTarget());
        }

        $content = '';

        if ($tagContent == '') {
            $content .= $this->selfClosingTag($tag, $attributes);
        } else {
            $content .= $this->openTag($tag, $attributes);
            $content .= $tagContent;
            $content .= $this->closeTag($tag);
        }

        return $content;
    }

    protected function applyDefaultLabelAlignment($flags, $default)
    {
        return (Nethgui_Renderer_Abstract::LABEL_NONE | Nethgui_Renderer_Abstract::LABEL_ABOVE | Nethgui_Renderer_Abstract::LABEL_LEFT | Nethgui_Renderer_Abstract::LABEL_RIGHT) & $flags ? $flags : $flags | $default;
    }

}

