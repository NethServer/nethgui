<?php
/**
 * @package Widget
 * @author Davide Principi <davide.principi@nethesis.it>
 * @internal
 */

/**
 * @internal
 * Abstract Xhtml Widget class
 */
abstract class Nethgui_Widget_Xhtml implements Nethgui_Renderer_WidgetInterface
{

    static private $instance = 0;
    private $children = array();
    private $attributes = array();
    /**
     * @var Nethgui_Core_ViewInterface 
     */
    protected $view;

    public function getAttribute($attribute, $default = NULL)
    {
        if ( ! $this->hasAttribute($attribute)) {
            return $default;
        }
        return $this->attributes[$attribute];
    }

    public function setAttribute($attribute, $value)
    {
        $this->attributes[$attribute] = $value;
        return $this;
    }

    protected function hasAttribute($attribute)
    {
        return array_key_exists($attribute, $this->attributes);
    }

    public function __construct(Nethgui_Core_ViewInterface $view)
    {
        $this->view = $view;
        self::$instance ++;
    }

    protected static function getInstanceCounter()
    {
        return self::$instance;
    }

    public function insert(Nethgui_Renderer_WidgetInterface $widget)
    {
        $this->children[] = $widget;
        return $this;
    }

    public function render()
    {
        return $this->renderChildren();
    }

    protected function renderChildren()
    {
        $output = '';
        foreach ($this->children as $child) {
            $output .= $this->wrapChild($child->render());
        }
        return $output;
    }

    protected function getChildren()
    {
        return $this->children;
    }

    protected function hasChildren()
    {
        return ! empty($this->children);
    }

    protected function wrapChild($childOutput)
    {
        return $childOutput;
    }

    public function __toString()
    {
        return $this->render();
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
        return sprintf('<%s%s />', strtolower($tag), $this->prepareXhtmlAttributes($attributes));
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

    /**
     * Push a LABEL tag for given control id
     *
     * @see http://www.w3.org/TR/html401/interact/forms.html#h-17.9.1
     * @param string $name
     * @param string $id
     * @return string
     */
    private function label($name, $id)
    {
        $content = '';
        $content .= $this->openTag('label', array('for' => $id));
        $content .= htmlspecialchars($this->view->translate($name . '_label'));
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
    protected function labeledControlTag($tag, $name, $label, $flags, $cssClass = '', $attributes = array(), $tagContent = '')
    {
        if (isset($attributes['id'])) {
            $controlId = $attributes['id'];
        } else {
            $controlId = $this->view->getUniqueId($name);
            $attributes['id'] = $controlId;
        }

        $wrapperClass = 'labeled-control';

        if ($flags & Nethgui_Renderer_Abstract::LABEL_RIGHT) {
            $wrapperClass .= ' label-right';
        } elseif ($flags & Nethgui_Renderer_Abstract::LABEL_ABOVE) {
            $wrapperClass .= ' label-above';
        } else {
            $wrapperClass .= ' label-left';
        }

        $content = '';

        $content .= $this->openTag('div', array('class' => $wrapperClass));

        if ($flags & Nethgui_Renderer_Abstract::LABEL_RIGHT) {
            $content .= $this->controlTag($tag, $name, $flags, $cssClass, $attributes, $tagContent);
            $content .= $this->label($label, $controlId);
        } else {
            $content .= $this->label($label, $controlId);
            $content .= $this->controlTag($tag, $name, $flags, $cssClass, $attributes, $tagContent);
        }
        $content .= $this->closeTag('div');

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
        $cssClass .= ' ' . $this->getClientEventTarget();
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

        if ( ! empty($cssClass)) {
            $attributes['class'] = $cssClass . (isset($attributes['class']) ? ' ' . $attributes['class'] : '');
        }

        $cssClass = trim($cssClass);

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
        return (Nethgui_Renderer_Abstract::LABEL_ABOVE | Nethgui_Renderer_Abstract::LABEL_LEFT | Nethgui_Renderer_Abstract::LABEL_RIGHT) & $flags ? $flags : $flags | $default;
    }

    /**
     *
     * @param array|string $_ Arguments for URL
     * @return string the URL
     */
    protected function buildUrl()
    {
        $parameters = array();
        $path = $this->view->getModulePath();

        foreach (func_get_args() as $arg) {
            if (is_array($arg)) {
                $parameters = array_merge($parameters, $arg);
            } else {
                $path[] = strval($arg);
            }
        }

        return Nethgui_Framework::getInstance()->buildUrl($path, $parameters);
    }

    protected function getClientEventTarget()
    {
        if ( ! $this->hasAttribute('name')) {
            throw new Nethgui_Exception_View('Missing `name` attribute');
        }

        return $this->view->getClientEventTarget($this->getAttribute('name'));
        
    }

}

