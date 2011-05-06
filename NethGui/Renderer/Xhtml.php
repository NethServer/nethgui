<?php
/**
 * @package Renderer
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * @package Renderer
 */
class NethGui_Renderer_Xhtml extends NethGui_Renderer_Abstract
{

    /**
     * Module path caches the identifier of all ancestors from the root to the
     * associated module.
     * @var array
     */
    private $modulePath;
    /**
     * @see setWrapTag()
     * @var string
     */
    private $wrapTag;
    private $content = array();
    private $flags = 0;

    public function __clone()
    {
        $this->content = array();
        $this->wrapTag = NULL;
    }

    public function render()
    {
        return $this->flushContent();
    }

    /**
     * Pushes a string or an object into the internal content buffer.
     * 
     * @param string|object $content A string or an object implementing __toString() magic method.
     * @return mixed The pushed string or object
     */
    private function pushContent($content)
    {
        $this->content[] = $content;
        return $content;
    }

    private function getFullName($name)
    {
        $path = $this->getModulePath();
        $path[] = $name;
        $prefix = array_shift($path);

        return $prefix . '[' . implode('][', $path) . ']';
    }

    private function getFullId($name)
    {
        return implode('_', $this->getModulePath()) . '_' . $name;
    }

    /**
     *
     * @param array|string $_ Arguments for URL
     * @return string the URL
     */
    public function buildUrl()
    {
        $parameters = array();
        $path = $this->getModulePath();

        foreach (func_get_args () as $arg) {
            if (is_string($arg)) {
                $path[] = $arg;
            } elseif (is_array($arg)) {
                $parameters = array_merge($parameters, $arg);
            }
        }

        return NethGui_Framework::getInstance()->buildUrl($path, $parameters);
    }

    /**
     * Gets the array of the current module identifier plus all identifiers of
     * the ancestor modules.     
     *
     * @return array
     */
    protected function getModulePath()
    {
        if ( ! isset($this->modulePath)) {
            $this->modulePath = array();

            $watchdog = 0;
            $module = $this->view->getModule();

            while ( ! (is_null($module))) {
                if ( ++ $watchdog > 20) {
                    throw new Exception("Too many nested modules or cyclic module structure.");
                }
                array_unshift($this->modulePath, $module->getIdentifier());
                $module = $module->getParent();
            }
        }

        return $this->modulePath;
    }

    /**
     * Get an XHTML opening tag string
     * 
     * @param string $tag The tag name (DIV, P, FORM...)
     * @param array $attributes The HTML attributes (id, name, for...)
     * @param string $content Raw content string
     * @return string
     */
    private function openTag($tag, $attributes = array())
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
    private function selfClosingTag($tag, $attributes)
    {
        return sprintf('<%s%s/>', strtolower($tag), $this->prepareXhtmlAttributes($attributes));
    }

    /**
     * Get an XHTML closing tag string
     *
     * @param string $tag Tag to be closed.
     * @return string
     */
    private function closeTag($tag)
    {
        return sprintf('</%s>', strtolower($tag));
    }

    /**
     * Convert an hash to a string of HTML tag attributes.
     *
     * htmlspecialchars is applied to all attribute values.
     *
     * @param array $attributes
     * @return string
     */
    private function prepareXhtmlAttributes($attributes)
    {
        $content = '';

        foreach ($attributes as $name => $value) {
            $content .= $name . '="' . htmlspecialchars($value) . '" ';
        }

        return ' ' . trim($content);
    }

    /**
     * Push a LABEL tag for given control id
     *
     * @see http://www.w3.org/TR/html401/interact/forms.html#h-17.9.1
     * @param string $name
     * @param string $id
     */
    private function label($name, $id)
    {
        $this->pushContent($this->openTag('label', array('for' => $id)));
        $this->pushContent(htmlspecialchars(T($name . '_label')));
        $this->pushContent($this->closeTag('label'));
    }

    /**
     * The given tag will wrap the renderer output, if not empty.
     *
     * @see flushContent()
     * @see contentTag()
     * @param string $tag
     * @param string $name
     * @param string $cssClass
     * @param array $attributes
     */
    private function setWrapTag($tag, $name, $cssClass = '', $attributes = array())
    {

        if ( ! isset($attributes['id'])) {
            $attributes['id'] = $this->getFullId($name);
        }

        if ( ! isset($attributes['class'])) {
            $attributes['class'] = $cssClass;
        }

        $this->wrapTag = array($tag, $attributes);
    }

    /**
     * Returns the string representing the object state, resetting it to empty.
     *
     * After this method has been invoked, the object is resetted to its initial
     * state, as if it's newly created.
     *
     * @see setWrapTag()
     * @return string
     */
    private function flushContent()
    {
        $content = implode('', $this->content);
        $this->content = array();

        if (strlen($content) > 0 && is_array($this->wrapTag)) {
            $content =
                $this->pushContent($this->openTag($this->wrapTag[0], $this->wrapTag[1])) .
                $content .
                $this->pushContent($this->closeTag($this->wrapTag[0]));

            $this->wrapTag = NULL;
        }

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
     */
    private function labeledControlTag($tag, $name, $label, $flags, $cssClass = '', $attributes = array())
    {
        if (isset($attributes['id'])) {
            $controlId = $attributes['id'];
        } else {
            $controlId = $this->getFullId($name);
            $attributes['id'] = $controlId;
        }

        $this->pushContent($this->openTag('div', array('class' => 'labeled-control')));
        if ($flags & self::LABEL_RIGHT) {
            $this->controlTag($tag, $name, $flags, $cssClass, $attributes);
            $this->label($label, $controlId);
        } else {
            $this->label($label, $controlId);
            $this->controlTag($tag, $name, $flags, $cssClass, $attributes);
        }
        $this->pushContent($this->closeTag('div'));
    }

    /**
     * Push an HTML tag for parameter $name.
     * 
     * @param string $tag The XHTML tag of the control.
     * @param string $name The name of the view parameter that holds the data
     * @param integer $flags Flags bitmask {STATE_CHECKED, STATE_DISABLED}
     * @param string $cssClass The `class` attribute value
     * @param array $attributes Generic attributes array See {@link openTag()}
     */
    private function controlTag($tag, $name, $flags, $cssClass = '', $attributes = array())
    {
        // Add default instance flags:
        $flags |= $this->flags;

        $tag = strtolower($tag);

        if ( ! isset($attributes['id'])) {
            $attributes['id'] = $this->getFullId($name);
        }

        if ($tag == 'input') {
            $attributes['name'] = $this->getFullName($name);

            $isCheckable = isset($attributes['type']) && ($attributes['type'] == 'checkbox' || $attributes['type'] == 'radio');

            if ($flags & self::STATE_CHECKED && $isCheckable) {
                $attributes['checked'] = 'checked';
            }
        }

        if (in_array($tag, array('input', 'button', 'textarea', 'select', 'optgroup', 'option'))
            && ($flags & self::STATE_DISABLED)) {
            $attributes['disabled'] = 'disabled';
        }


        $cssClass = trim($cssClass);

        if ( ! empty($cssClass)) {
            $attributes['class'] = $cssClass;
        }

        $this->pushContent($this->selfClosingTag($tag, $attributes));
    }

    //
    // Controls
    //

    public function append($text, $hsc = TRUE)
    {
        if ($hsc) {
            $text = htmlspecialchars($text);
        }
        $this->pushContent($text);
        return $this;
    }

    public function inset($offset)
    {
        $value = $this->view[$offset];
        if ( ! $value instanceof NethGui_Core_ViewInterface) {
            $value = htmlspecialchars($value);
        }
        $this->pushContent($value);
        return $this;
    }

    public function button($name, $flags = 0, $value = NULL)
    {

        $attributes = array();

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
                $cssClass = 'button cancel';
            } else {
                $cssClass = 'button link';
            }

            if ( ! is_array($value)) {
                $value = array($value);
            }

            $url = call_user_func_array(array($this, 'buildUrl'), $value);
            $attributes['href'] = $url;

            $this->pushContent($this->openTag('a', $attributes));
            $this->append(T($buttonLabel));
            $this->pushContent($this->closeTag('a'));
        } else {

            if ($flags & self::BUTTON_SUBMIT) {
                $attributes['type'] = 'submit';
                $attributes['value'] = T($buttonLabel);
            } elseif ($flags & self::BUTTON_RESET) {
                $attributes['type'] = 'reset';
            } elseif ($flags & self::BUTTON_CUSTOM) {
                $attributes['type'] = 'button';
            }

            $this->controlTag('input', $name, $flags, 'button', $attributes);
        }

        return $this;
    }

    public function hidden($name, $flags = 0)
    {
        $attributes = array(
            'type' => 'hidden',
            'value' => $this->view[$name],
        );
        $this->controlTag('input', $name, $flags, '', $attributes);
        return $this;
    }

    public function radioButton($name, $value, $flags = 0)
    {
        $attributes = array(
            'type' => 'radio',
            'value' => $value,
            'id' => $this->getFullId($name . '_' . $value)
        );

        if ($value === $this->view[$name]) {
            $flags |= self::STATE_CHECKED;
        }

        $this->labeledControlTag('input', $name, $name . '_' . $value, $flags, '', $attributes);

        return $this;
    }

    public function checkBox($name, $value, $flags = 0)
    {
        $attributes = array(
            'type' => 'checkbox',
            'value' => $value,
        );

        if ($value === $this->view[$name]) {
            $flags |= self::STATE_CHECKED;
        }

        $this->labeledControlTag('input', $name, $name, $flags, '', $attributes);

        return $this;
    }

    public function textInput($name, $flags = 0)
    {
        $attributes = array(
            'value' => $this->view[$name],
            'type' => 'text',
        );

        $this->labeledControlTag('input', $name, $name, $flags, '', $attributes);

        return $this;
    }

    //
    // Containers (CLONED)
    //

    public function dialog($identifier, $flags = 0)
    {
        $className = 'dialog ';

        if ($flags & self::DIALOG_EMBEDDED) {
            $className .= 'embedded';

            // unset the EMBEDDED flag:
            $flags ^= self::DIALOG_EMBEDDED;
        } elseif ($flags & self::DIALOG_MODAL) {
            $className .= 'modal';

            // unset the MODAL flag:
            $flags ^= self::DIALOG_MODAL;
        } else {
            $className .= 'embedded'; // default dialog class
        }

        if ($flags & self::STATE_DISABLED) {
            $className .= ' disabled';
        }

        $dialog = $this->createNewInstance($flags);
        $this->pushContent($dialog);
        $dialog->setWrapTag('div', $identifier, $className);
        return $dialog;
    }

    public function form($action, $flags = 0)
    {
        $form = $this->createNewInstance($flags);
        $this->pushContent($form);

        $attributes = array(
            'method' => 'post',
            'action' => $form->buildUrl($action)
        );

        $form->setWrapTag('form', $action, 'apply-changes', $attributes);
       
        return $form;
    }

    public function tabs($name, $pages = NULL, $flags = 0)
    {
        $tabs = $this->createNewInstance($flags);
        $this->pushContent($tabs);
        $tabs->setWrapTag('div', $name, 'tabs');

        if (is_array($pages)) {
            foreach ($pages as $page) {
                $tabs->inset($page);
            }
        }

        return $tabs;
    }

    public function fieldsetSwitch($name, $value, $flags = 0)
    {
        $this->pushContent($this->openTag('div', array('class'=>'fieldset-switch')));
        $this->radioButton($name, $value, $flags);
        $fieldset = $this->createNewInstance($flags);
        $this->pushContent($fieldset);
        $fieldset->setWrapTag('fieldset', $name . '_' . $value . '_fieldset', '');
        $this->pushContent($this->closeTag('div'));
        return $fieldset;
    }

    /**
     * @return NethGui_Renderer_Xhtml
     */
    private function createNewInstance($flags = 0)
    {
        $instance = clone $this;
        $instance->flags = $flags;
        return $instance;
    }

}
