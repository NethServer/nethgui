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
    private $wrapTag;

    protected function getFullName($name)
    {
        $path = $this->getModulePath();
        $path[] = $name;
        $prefix = array_shift($path);

        return $prefix . '[' . implode('][', $path) . ']';
    }

    protected function getFullId($name)
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
     * public function getModulePath();
     * XXX: test
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
     *
     * @param string $tag The tag name (DIV, P, FORM...)
     * @param array $attributes The HTML attributes (id, name, for...)
     * @param string $content Raw content string
     */
    private function openTag($tag, $attributes = array())
    {
        $tag = strtolower($tag);
        $attributeString = $this->prepareXhtmlAttributes($attributes);
        $this->pushContent(sprintf('<%s%s>', $tag, $attributeString));
    }

    private function selfClosingTag($tag, $attributes)
    {
        $this->pushContent(sprintf('<%s%s/>', strtolower($tag), $this->prepareXhtmlAttributes($attributes)));
    }

    private function closeTag($tag)
    {
        $this->pushContent(sprintf('</%s>', strtolower($tag)));
    }

    private function label($name, $id)
    {
        $this->openTag('label', array('for' => $id));
        $this->pushContent(htmlspecialchars(T($name . '_label')));
        $this->closeTag('label');
    }

    private function prepareXhtmlAttributes($attributes)
    {
        $content = '';

        foreach ($attributes as $name => $value) {
            $content .= $name . '="' . htmlspecialchars($value) . '" ';
        }

        return ' ' . trim($content);
    }

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

    protected function flushContent()
    {
        $content = parent::flushContent();

        if (strlen($content) > 0 && is_array($this->wrapTag)) {
            $this->openTag($this->wrapTag[0], $this->wrapTag[1]);
            $this->pushContent($content);
            $this->closeTag($this->wrapTag[0]);
            $this->wrapTag = NULL;
            $content = parent::flushContent();
        }

        return $content;
    }

    private function labeledControlTag($tag, $name, $label, $flags, $cssClass = '', $attributes = array())
    {

        if (isset($attributes['id'])) {
            $controlId = $attributes['id'];
        } else {
            $controlId = $this->getFullId($name);
            $attributes['id'] = $controlId;
        }

        $this->openTag('div', array('class' => 'labeled-control'));
        if ($flags & (self::LABEL_ABOVE | self::LABEL_LEFT)) {
            $this->label($label, $controlId);
            $this->controlTag($tag, $name, $flags, $cssClass, $attributes);
        } else {
            $this->controlTag($tag, $name, $flags, $cssClass, $attributes);
            $this->label($label, $controlId);
        }
        $this->closeTag('div');
    }

    private function controlTag($tag, $name, $flags, $cssClass = '', $attributes = array())
    {
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

        $this->selfClosingTag($tag, $attributes);
    }

    //
    // Controls
    //

    public function inset($offset)
    {
        $this->pushContent($this->view[$offset]);
        return $this;
    }

    public function button($name, $flags = 0, $value = NULL)
    {

        $attributes = array();

        if ($flags & (self::BUTTON_LINK | self::BUTTON_CANCEL)) {

            if (is_null($value)) {
                $value = '..';
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

            $this->openTag('a', $attributes);
            $this->pushContent(htmlspecialchars(T($name)));
            $this->closeTag('a');
        } else {

            if ($flags & self::BUTTON_SUBMIT) {
                $attributes['type'] = 'submit';
                $attributes['value'] = T($name);
            } elseif ($flags & self::BUTTON_RESET) {
                $attributes['type'] = 'reset';
            } elseif ($flags & self::BUTTON_CUSTOM) {
                $attributes['type'] = 'button';
            }

            $this->controlTag('input', $name, $flags, 'button', $attributes);
        }

        return $this;
    }

    public function hidden($name, $value, $flags = 0)
    {
        $this->controlTag('input', $name, $flags, '', array('type' => 'hidden'));
        return $this;
    }

    public function radioButton($name, $value, $flags = 0)
    {
        $attributes = array(
            'type' => 'radio',
            'value' => $value,
            'id' => $this->getFullId($name . '_' . htmlspecialchars($value))
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

    public function dialog($name, $message = '', $flags = 0)
    {
        $className = 'dialog ';

        if ($flags & self::DIALOG_EMBEDDED) {
            $className .= 'embedded';
        } elseif ($flags & self::DIALOG_MODAL) {
            $className .= 'modal';
        } else {
            $className .= 'embedded'; // default dialog class
        }

        $dialog = $this->pushContent(clone $this);

        $dialog->setWrapTag('div', $name, $className);

        if (strlen($message) > 0) {
            $dialog->openTag('span', array('class' => 'message'));
            $dialog->pushContent(htmlspecialchars($message));
            $dialog->closeTag('span');
        }

        return $dialog;
    }

    public function form($name, $action = NULL)
    {
        $form = $this->pushContent(clone $this);

        if (is_null($action)) {
            $action = array($name);
        }

        $attributes = array(
            'method' => 'post',
            'action' => call_user_func_array(array($this, 'buildUrl'), $action)
        );

        $form->setWrapTag('form', $name, 'apply-changes', $attributes);

        return $form;
    }

    public function tabs($name, $pages = NULL)
    {
        $tabs = $this->pushContent(clone $this);


        $this->setWrapTag('div', $name, 'tabs');

        if (is_array($pages)) {
            foreach ($pages as $page) {
                $this->inset($page);
            }
        }

        return $this;
    }

    public function fieldsetSwitch($name, $value, $flags = 0)
    {
        $this->radioButton($name, $value, $flags);
        $fieldset = $this->pushContent(clone $this);
        $fieldset->setWrapTag('fieldset', array('class' => 'fieldset-switch'));
        return $fieldset;
    }

}
