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
     * @see addWrapTag()
     * @var string
     */
    private $wrapTag = array();
    private $content = array();
    private $flags = 0;

    public function __clone()
    {
        $this->content = array();
        $this->wrapTag = array();
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

    public function getFullId($name = NULL)
    {
        $prefix = implode('_', $this->getModulePath());

        if (empty($name)) {
            return $prefix;
        }
        return $prefix . '_' . $name;
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

        foreach (func_get_args() as $arg) {
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

        foreach ($attributes as $name => $value) {
            if ($value === FALSE) {
                continue;
            }
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
     * @param string $tag The XHTML tag name
     * @param string $localId The local module identifier of the wrap
     * @param string $cssClass Optional - The XHTML class attribute value
     * @param array $attributes Optional - Other XHTML attributes
     */
    private function addWrapTag($tag, $localId, $cssClass = '', $attributes = array())
    {

        if ( ! isset($attributes['id'])) {
            $attributes['id'] = $this->getFullId($localId);
        }

        if ( ! isset($attributes['class']) && ! empty($cssClass)) {
            $attributes['class'] = $cssClass;
        }

        $this->wrapTag[] = array($tag, $attributes);
    }

    /**
     * Returns the string representing the object state, resetting it to empty.
     *
     * After this method has been invoked, the object is resetted to its initial
     * state, as if it's newly created.
     *
     * @see addWrapTag()
     * @return string
     */
    private function flushContent()
    {
        $preWrap = '';
        $postWrap = '';

        // Implode all the content (converting each part into a String).
        $content = implode('', $this->content);

        // Reset content chain.
        $this->content = array();

        // Apply wrap only if we have content:
        if (strlen($content)) {

            // Concatenate PRE and POST wrap parts:
            foreach ($this->wrapTag as $tag) {
                $preWrap = $preWrap . $this->openTag($tag[0], $tag[1]);
                $postWrap = $this->closeTag($tag[0]) . $postWrap;
            }
        }

        // Reset the wrap stack:
        $this->wrapTag = array();

        // Return wrapped content:
        return $preWrap . $content . $postWrap;
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

        if (in_array($tag, array('input', 'button', 'textarea', 'select', 'optgroup', 'option'))) {
            if ($flags & self::STATE_DISABLED) {
                $attributes['disabled'] = 'disabled';
            }

            if ($flags & self::STATE_VALIDATION_ERROR) {
                $cssClass .= ' validation-error';
            }
        }

        if ( ! empty($cssClass)) {
            $attributes['class'] = $cssClass . (isset($attributes['class']) ? ' ' . $attributes['class'] : '');
        }

        $cssClass = trim($cssClass);

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

            $this->pushContent($this->openTag('a', $attributes));
            $this->append(T($buttonLabel));
            $this->pushContent($this->closeTag('a'));
        } else {

            if ($flags & self::BUTTON_SUBMIT) {
                $attributes['type'] = 'submit';
                $cssClass .= ' submit';
                $attributes['value'] = T($buttonLabel);
            } elseif ($flags & self::BUTTON_RESET) {
                $attributes['type'] = 'reset';
                $cssClass .= ' reset';
            } elseif ($flags & self::BUTTON_CUSTOM) {
                $attributes['type'] = 'button';
                $cssClass .= ' custom';
            }

            $this->controlTag('input', $name, $flags, $cssClass, $attributes);
        }

        return $this;
    }

    public function hidden($name, $flags = 0, $forceValue = NULL)
    {
        $attributes = array(
            'type' => 'hidden',
            'value' => is_null($forceValue) ? strval($this->view[$name]) : strval($forceValue),
        );
        $this->controlTag('input', $name, $flags, '', $attributes);
        return $this;
    }

    public function radioButton($name, $value, $flags = 0)
    {
        $attributes = array(
            'type' => 'radio',
            'value' => strval($value),
            'id' => $this->getFullId($name . '_' . $value)
        );

        if ($value === $this->view[$name]) {
            $flags |= self::STATE_CHECKED;
        }

        $flags = $this->applyDefaultLabelAlignment($flags, self::LABEL_RIGHT);

        $this->labeledControlTag('input', $name, $name . '_' . $value, $flags, '', $attributes);

        return $this;
    }

    public function checkBox($name, $value, $flags = 0)
    {
        $attributes = array(
            'type' => 'checkbox',
            'value' => strval($value),
        );

        if ($value == $this->view[$name]) {
            $flags |= self::STATE_CHECKED;
        }

        $flags = $this->applyDefaultLabelAlignment($flags, self::LABEL_RIGHT);

        $this->labeledControlTag('input', $name, $name, $flags, '', $attributes);

        return $this;
    }

    public function textInput($name, $flags = 0)
    {
        $attributes = array(
            'value' => strval($this->view[$name]),
            'type' => 'text',
        );

        $flags = $this->applyDefaultLabelAlignment($flags, self::LABEL_LEFT);

        // Check if $name is in the list of invalid parameters.
        if (isset($this->view['__invalidParameters']) && in_array($name, $this->view['__invalidParameters'])) {
            $flags |= self::STATE_VALIDATION_ERROR;
        }

        $this->labeledControlTag('input', $name, $name, $flags, '', $attributes);

        return $this;
    }

    //
    // Containers (CLONED)
    //

    public function dialog($identifier, $flags = 0)
    {
        $className = 'dialog';

        if ($flags & self::DIALOG_SUCCESS) {
            $className .= ' success';
        } elseif ($flags & self::DIALOG_WARNING) {
            $className .= ' warning';
        } elseif ($flags & self::DIALOG_ERROR) {
            $className .= ' error';
        }

        if ($flags & self::DIALOG_EMBEDDED) {
            $className .= ' embedded';
            // unset the EMBEDDED flag:
            $flags ^= self::DIALOG_EMBEDDED;
        } elseif ($flags & self::DIALOG_MODAL) {
            $className .= ' modal';
            // unset the MODAL flag:
            $flags ^= self::DIALOG_MODAL;
        } else {
            $className .= ' embedded'; // default dialog class
        }

        if ($flags & self::STATE_DISABLED) {
            $className .= ' disabled';
        }

        $dialog = $this->createNewInstance($flags);
        $this->pushContent($dialog);
        $dialog->addWrapTag('div', $identifier, $className);
        return $dialog;
    }

    public function form($action = '', $flags = 0, $id = NULL)
    {
        $form = $this->createNewInstance($flags);
        $this->pushContent($form);

        $attributes = array(
            'method' => 'post',
            'action' => $form->buildUrl($action),
            'id' => FALSE, // This ensures ID attribute is not emitted. See prepareXhtmlAttributes().
        );

        $form->addWrapTag('form', $action, '', $attributes);
        $form->addWrapTag('div', is_null($id) ? $action : $id);

        return $form;
    }

    public function tabs($name, $pages = NULL, $flags = 0)
    {
        $tabs = $this->createNewInstance($flags);
        $this->pushContent($tabs);
        $tabs->addWrapTag('div', $name, 'tabs');

        if (is_array($pages)) {
            $tabs->pushContent($this->openTag('ul', array('class' => 'tabs-list')));
            foreach ($pages as $page) {

                $tabs->pushContent($this->openTag('li'));
                $tabs->pushContent($this->openTag('a', array('href' => '#' . $tabs->getFullId($page))));
                $tabs->pushContent(T($page . '_Title'));
                $tabs->pushContent($this->closeTag('a'));
                $tabs->pushContent($this->closeTag('li'));
            }
            $tabs->pushContent($this->closeTag('ul'));
        }

        foreach ($pages as $page) {
            $tabs->panel($page)->inset($page);
        }

        return $tabs;
    }

    public function fieldsetSwitch($name, $value, $flags = 0)
    {
        $this->pushContent($this->openTag('div', array('class' => 'fieldset-switch')));
        $this->radioButton($name, $value, $flags);
        $fieldset = $this->createNewInstance($flags);
        $this->pushContent($fieldset);
        $fieldset->addWrapTag('fieldset', $name . '_' . $value . '_fieldset', '');
        $this->pushContent($this->closeTag('div'));
        return $fieldset;
    }

    public function panel($identifier = 'panel', $flags = 0)
    {
        $panel = $this->createNewInstance($flags);
        $this->pushContent($panel);
        $panel->addWrapTag('div', $identifier, 'panel');

        return $panel;
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

    private function applyDefaultLabelAlignment($flags, $default)
    {
        return (self::LABEL_ABOVE | self::LABEL_LEFT | self::LABEL_RIGHT) & $flags ? $flags : $flags | $default;
    }

}
