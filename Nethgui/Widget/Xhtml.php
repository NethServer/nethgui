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
        $attributes = array(
            'for' => $id,
            'class' => $this->getAttribute('helpId', $this->getAttribute('name', FALSE))
        );

        $content = '';
        $content .= $this->openTag('label', $attributes);
        $content .= htmlspecialchars($this->view->translate($text));
        $content .= $this->closeTag('label');
        return $content;
    }

    /**
     *
     * @see controlTag()
     * @param string $label The label text
     * @param string $tag The XHTML tag of the control.
     * @param string $name The name of the view parameter that holds the data
     * @param integer $flags Flags bitmask {STATE_CHECKED, STATE_DISABLED, LABEL_*}
     * @param string $cssClass The `class` attribute value
     * @param array $attributes  Generic attributes array See {@link openTag()}
     * @param string $tagContent The content of the tag. An empty string results in a self-closing tag.
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

        if ($flags & Nethgui_Renderer_WidgetFactoryInterface::LABEL_NONE) {
            $content .= $this->controlTag($tag, $name, $flags, $cssClass, $attributes, $tagContent);
        } else {

            if ($flags & Nethgui_Renderer_WidgetFactoryInterface::LABEL_RIGHT) {
                $wrapperClass .= ' label-right';
                $content .= $this->openTag('div', array('class' => $wrapperClass));
                $content .= $this->controlTag($tag, $name, $flags, $cssClass, $attributes, $tagContent);
                $content .= $this->label($label, $controlId);
                $content .= $this->closeTag('div');
            } else {
                if ($flags & Nethgui_Renderer_WidgetFactoryInterface::LABEL_ABOVE) {
                    $wrapperClass .= ' label-above';
                } elseif ($flags & Nethgui_Renderer_WidgetFactoryInterface::LABEL_LEFT) {
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
            $attributes['name'] = $this->getControlName($name);
        }

        $isCheckable = ($tag == 'input') && isset($attributes['type']) && ($attributes['type'] == 'checkbox' || $attributes['type'] == 'radio');

        if ($flags & Nethgui_Renderer_WidgetFactoryInterface::STATE_CHECKED && $isCheckable) {
            $attributes['checked'] = 'checked';
        }
        if ($flags & Nethgui_Renderer_WidgetFactoryInterface::STATE_READONLY) {
            if ($isCheckable) {
                // `readonly` attribute is not appliable to checkable controls
                $attributes['disabled'] = 'disabled';
            } else {
                $attributes['readonly'] = 'readonly';
            }
        }

        if ($tag == 'button') {
            if (empty($tagContent)) {
                $tagContent = $attributes['value'];
            }
        }

        if (in_array($tag, array('input', 'button', 'textarea', 'select', 'optgroup', 'option'))) {
            if ($flags & Nethgui_Renderer_WidgetFactoryInterface::STATE_DISABLED) {
                $attributes['disabled'] = 'disabled';
            }

            if ($flags & Nethgui_Renderer_WidgetFactoryInterface::STATE_VALIDATION_ERROR) {
                $cssClass .= ' validation-error ui-state-error';
            }
        }

        if ( ! isset($attributes['class'])) {
            $attributes['class'] = trim($cssClass . ' ' . $this->getClientEventTarget());
        }

        $content = '';

        if ($tagContent == '' && $tag !== 'textarea') {
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
        return (Nethgui_Renderer_WidgetFactoryInterface::LABEL_NONE | Nethgui_Renderer_WidgetFactoryInterface::LABEL_ABOVE | Nethgui_Renderer_WidgetFactoryInterface::LABEL_LEFT | Nethgui_Renderer_WidgetFactoryInterface::LABEL_RIGHT) & $flags ? $flags : $flags | $default;
    }

    /**
     * Get an XHTML opening tag string
     *
     * @param string $tag The tag name (DIV, P, FORM...)
     * @param array $attributes The HTML attributes (id, name, for...)
     * @param string $content Raw content string
     * @return string
     */
    protected function openTag($tag, $attributes = array())
    {
        $tag = strtolower($tag);
        $attributeString = $this->prepareXhtmlAttributes($attributes);
        return sprintf('<%s%s>', $tag, $attributeString);
    }

    /**
     * Get an XHTML self-closing tag string
     *
     * @see openTag()
     * @param string $tag
     * @param array $attributes
     * @return string
     */
    protected function selfClosingTag($tag, $attributes)
    {
        $tag = strtolower($tag);
        return sprintf('<%s%s />', $tag, $this->prepareXhtmlAttributes($attributes));
    }

    /**
     * Get an XHTML closing tag string
     *
     * @param string $tag Tag to be closed.
     * @return string
     */
    protected function closeTag($tag)
    {
        return sprintf('</%s>', strtolower($tag));
    }

    /**
     * Convert an hash to a string of HTML tag attributes.
     *
     * - htmlspecialchars() is applied to all attribute values.
     * - A FALSE value ensures the attribute is not set.
     *
     * @see htmlspecialchars()
     * @param array $attributes
     * @return string
     */
    private function prepareXhtmlAttributes($attributes)
    {
        $content = '';

        foreach ($attributes as $attribute => $value) {
            if ($value === FALSE) {
                continue;
            }
            $content .= $attribute . '="' . htmlspecialchars($value) . '" ';
        }

        return ' ' . trim($content);
    }


    private function realPath($name)
    {
        if (is_array($name)) {
            // ensure the $name argument is a string in the form of ../segment1/../segment2/..
            $name = implode('/', $name);
        }

        if (strlen($name) > 0 && $name[0] == '/') {
            // if the first character is a / consider an absolute path
            $nameSegments = array();
        } else {
            // else consider a path relative to the current module
            $nameSegments = $this->view->getModulePath();
        }

        // split the path into its parts
        $parts = explode('/', $name);

        foreach ($parts as $part) {
            if ($part == '') {
                continue; // skip empty parts
            } elseif ($part == '..') {
                array_pop($nameSegments); // backreference
            } else {
                $nameSegments[] = $part; // add segment
            }
        }

        return $nameSegments;
    }

    /**
     * Generate a control name for the given $parts. If no parts are given
     * the name is generated from the module referenced by the view.
     *
     * @param string|array $parts
     * @return string
     */
    public function getControlName($parts = '')
    {
        $nameSegments = $this->realPath($parts);
        $prefix = array_shift($nameSegments); // the first segment is not wrapped into square brackets
        return $prefix . '[' . implode('][', $nameSegments) . ']';
    }


}

