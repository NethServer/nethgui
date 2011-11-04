<?php
/**
 * @package Widget
 * @author Davide Principi <davide.principi@nethesis.it>
 * @ignore
 */

/**
 * Abstract Widget class
 * @ignore
 */
class Nethgui_Widget_Abstract implements Nethgui_Renderer_WidgetInterface
{

    static private $instance = 0;
    private $children = array();
    private $attributes = array();

    /**
     * @var Nethgui_Renderer_Abstract
     */
    protected $view;

    public function __construct(Nethgui_Renderer_Abstract $view)
    {
        $this->view = $view;
        self::$instance ++;
    }

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

    public function hasAttribute($attribute)
    {
        return array_key_exists($attribute, $this->attributes);
    }

    protected static function getInstanceCounter()
    {
        return self::$instance;
    }

    protected function prepend(Nethgui_Renderer_WidgetInterface $widget)
    {
        array_unshift($this->children, $widget);
        return $this;
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
        $flags = $this->getAttribute('flags', 0) & NETHGUI_INHERITABLE_FLAGS;
        $output = '';
        foreach ($this->children as $child) {
            if ($child->hasAttribute('flags')) {
                $child->setAttribute('flags', $flags | $child->getAttribute('flags'));
            }
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
        try {
            return $this->render();
        } catch (Exception $ex) {
            Nethgui_Framework::getInstance()->logMessage($ex->getMessage());
            throw $ex;
        }
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
        return sprintf('<%s%s></%s>', $tag, $this->prepareXhtmlAttributes($attributes), $tag);
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
     *
     * @param array|string $_ Arguments for URL
     * @return string the URL
     */
    protected function buildUrl()
    {
        $parameters = array();
        $path = array();

        foreach (func_get_args() as $arg) {
            if (is_array($arg)) {
                $parameters = array_merge($parameters, $arg);
            } else {
                $path[] = strval($arg);
            }
        }

        if (count($path) > 0 && substr($path[0], 0, 1) != '/') {
            $path = array_merge($this->view->getModulePath(), $path);
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