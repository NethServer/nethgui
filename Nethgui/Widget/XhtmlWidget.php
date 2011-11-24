<?php
namespace Nethgui\Widget;

/*
 * Copyright (C) 2011 Nethesis S.r.l.
 * 
 * This script is part of NethServer.
 * 
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Abstract Xhtml Widget class
 */
abstract class XhtmlWidget extends AbstractWidget
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

        if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::LABEL_NONE) {
            $content .= $this->controlTag($tag, $name, $flags, $cssClass, $attributes, $tagContent);
        } else {

            if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::LABEL_RIGHT) {
                $wrapperClass .= ' label-right';
                $content .= $this->openTag('div', array('class' => $wrapperClass));
                $content .= $this->controlTag($tag, $name, $flags, $cssClass, $attributes, $tagContent);
                $content .= $this->label($label, $controlId);
                $content .= $this->closeTag('div');
            } else {
                if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::LABEL_ABOVE) {
                    $wrapperClass .= ' label-above';
                } elseif ($flags & \Nethgui\Renderer\WidgetFactoryInterface::LABEL_LEFT) {
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

        if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::STATE_CHECKED && $isCheckable) {
            $attributes['checked'] = 'checked';
        }
        if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::STATE_READONLY) {
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
            if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::STATE_DISABLED) {
                $attributes['disabled'] = 'disabled';
            }

            if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::STATE_VALIDATION_ERROR) {
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
        return (\Nethgui\Renderer\WidgetFactoryInterface::LABEL_NONE | \Nethgui\Renderer\WidgetFactoryInterface::LABEL_ABOVE | \Nethgui\Renderer\WidgetFactoryInterface::LABEL_LEFT | \Nethgui\Renderer\WidgetFactoryInterface::LABEL_RIGHT) & $flags ? $flags : $flags | $default;
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

    /**
     * Generate a control name for the given $parts. If no parts are given
     * the name is generated from the module referenced by the view.
     * 
     * @param string $parts
     * @return string
     */
    protected function getControlName($parts = '')
    {
        $nameSegments = $this->view->resolvePath($parts);
        $prefix = array_shift($nameSegments); // the first segment is not wrapped into square brackets
        return $prefix . '[' . implode('][', $nameSegments) . ']';
    }

    public function render()
    {
        /*
         * Refs #620
         * Client commands are applied to the designed widget to keep the view consistent
         * in both CLIENT and SERVER modes.
         */
        $this->invokeCommands();
        parent::render();
    }

    private function invokeCommands()
    {
        if ( ! $this->hasAttribute('receiver')) {
            return;
        }

        $commandBuckets = array_map('trim', explode(',', $this->getAttribute('receiver')));

        foreach ($commandBuckets as $commandBucket) {
            if ( ! isset($this->view[$commandBucket])) {
                continue;
            }

            $command = $this->view[$commandBucket];

            if ($command instanceof \Nethgui\Core\CommandInterface
                && ! $command->isExecuted()) {
                $command->setReceiver($this)->execute();
            }
        }
    }

    protected function appendReceiverName($cssClass)
    {
        if ($this->hasAttribute('receiver')) {
            $cssClass .= ' ' . $this->view->getClientEventTarget($this->getAttribute('receiver'));
        }

        return $cssClass;
    }

}

