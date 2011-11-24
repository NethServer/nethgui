<?php
/**
 * @author Davide Principi <davide.principi@nethesis.it>
 * @ignore
 */

namespace Nethgui\Widget;

/**
 * Abstract Widget class
 * @ignore
 */
abstract class AbstractWidget implements \Nethgui\Renderer\WidgetInterface, \Nethgui\Log\LogConsumerInterface
{

    static private $instance = 0;
    private $children = array();
    private $attributes = array();

    /**
     * @var \Nethgui\Renderer\Xhtml
     */
    protected $view;

    public function __construct(\Nethgui\Renderer\Xhtml $view)
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

    protected function prepend(\Nethgui\Renderer\WidgetInterface $widget)
    {
        array_unshift($this->children, $widget);
        return $this;
    }

    public function insert(\Nethgui\Renderer\WidgetInterface $widget)
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
            $this->getLog()->exception($ex, TRUE);
            //throw $ex;
            return '';
        }

    }

    protected function getClientEventTarget()
    {
        if ( ! $this->hasAttribute('name')) {
            throw new \Nethgui\Exception\View('Missing `name` attribute');
        }

        return $this->view->getClientEventTarget($this->getAttribute('name'));
    }

    public function setLog(\Nethgui\Log\AbstractLog $log)
    {
        throw new Exception(sprintf('Cannot invoke setLog() on %s', get_class($this)));
    }

    public function getLog()
    {
        return $this->view->getLog();
    }

}
