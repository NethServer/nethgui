<?php
/**
 * @package Renderer
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * A decorator that enhances a View with rendering capabilities
 * and forbids changes.
 *
 * @see http://en.wikipedia.org/wiki/Decorator_pattern
 * @package Renderer
 */
abstract class NethGui_Renderer_Abstract implements NethGui_Core_ViewInterface
{
    const LABEL_LEFT = 0x01;
    const LABEL_RIGHT = 0x02;
    const LABEL_ABOVE = 0x04;

    const STATE_CHECKED = 0x08;
    const STATE_DISABLED = 0x10;
    const STATE_VALIDATION_ERROR = 0x20;

    const BUTTON_SUBMIT = 0x40;
    const BUTTON_CANCEL = 0x80;
    const BUTTON_RESET = 0x100;
    const BUTTON_LINK = 0x200;
    const BUTTON_CUSTOM = 0x400;

    const DIALOG_MODAL = 0x800;
    const DIALOG_EMBEDDED = 0x1000;

    /**
     *
     * @var NethGui_Core_ViewInterface
     */
    protected $view;
    private $content = array();

    public function __construct(NethGui_Core_ViewInterface $view)
    {
        $this->view = $view;
    }

    public function __toString()
    {
        return $this->flushContent();
    }

    protected function pushContent($content)
    {
        $this->content[] = $content;
        return $content;
    }

    protected function flushContent()
    {
        $v = implode('', $this->content);
        $this->content = array();
        return $v;
    }

    protected function hasContent() {
        return !empty($this->content);
    }

    public function __clone()
    {
        $this->content = array();
    }

    public function copyFrom($data)
    {
        throw new NethGui_Exception_View('Cannot change the view values');
    }

    public function getIterator()
    {
        return $this->view->getIterator($data);
    }

    public function getModule()
    {
        return $this->view->getModule();
    }

    public function offsetExists($offset)
    {
        return $this->view->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->view->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        throw new NethGui_Exception_View('Cannot change the view value');
    }

    public function offsetUnset($offset)
    {
        throw new NethGui_Exception_View('Cannot unset a view value');
    }

    public function render()
    {
        throw new NethGui_Exception_View('Cannot render a renderer object');
    }

    public function setTemplate($template)
    {
        throw new NethGui_Exception_View('Cannot change the view template');
    }

    public function spawnView(NethGui_Core_ModuleInterface $module, $register = FALSE)
    {
        throw new NethGui_Exception_View('Cannot spawn a view now');
    }

    /**
     * Renders the View member
     * @return NethGui_Renderer_Abstract
     */
    abstract public function inset($offset);

    abstract public function textInput($name, $flags = 0);

    abstract public function hidden($name, $value, $flags = 0);

    abstract public function radioButton($name, $value, $flags = 0);

    abstract public function checkBox($name, $value, $flags = 0);

    abstract public function button($name, $flags = 0, $value = NULL);

    /**
     * Renders a dialog box container.
     *
     * @param string $name The identifier of the control
     * @param int $flags Render flags: {DIALOG_MODAL, DIALOG_EMBEDDED, STATE_DISABLED}
     * @return NethGui_Renderer_Abstract
     */
    abstract public function dialog($name, $message = '', $flags = 0);

    /**
     * Renders a tab container.
     *
     * @param string $name The identifier of the control
     * @param array $pages Optional - The identifier list of the pages. NULL includes all the sub-views of the current object.
     */
    abstract public function tabs($name, $pages = NULL);

    /**
     * Renders a simple form container.
     */
    abstract public function form($name, $action = NULL);

    /**
     * Renders a selectable fieldset container
     */
    abstract public function fieldsetSwitch($name, $value, $flags = 0);
}
