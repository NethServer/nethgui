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
 * An abstract widget that renders an XHTML string
 * 
 * @api
 */
abstract class XhtmlWidget extends AbstractWidget implements \Nethgui\View\CommandReceiverInterface
{

    /**
     * Get the object that renders the widget's view.
     * 
     * @api
     * @return \Nethgui\Renderer\Xhtml
     */
    protected function getRenderer()
    {
        return $this->view;
    }

    /**
     * Get a LABEL tag
     *
     * @see http://www.w3.org/TR/html401/interact/forms.html#h-17.9.1
     * @param string $name
     * @param string $id
     * @return string
     */
    private function label($text, $id)
    {
        $labelWidget = new Xhtml\TextLabel($this->view);
        call_user_func($this->di, $labelWidget);

        $labelWidget
            ->setAttribute('template', $text)
            ->setAttribute('tag', 'label')
            ->setAttribute('class', $this->getAttribute('helpId', $this->view->getUniqueId($this->getAttribute('name', FALSE))))
            ->setAttribute('htmlAttributes', array('for' => $id))
        ;

        if ($this->hasAttribute('labelSource')) {
            $labelWidget->setAttribute('name', $this->getAttribute('labelSource'));
        }

        if ($this->hasAttribute('flags')) {
            $labelWidget->setAttribute('flags', $this->getAttribute('flags'));
        }

        return $labelWidget->render();
    }

    /**
     * Generate XHTML markup fragment for a FORM control with a LABEL tag
     * 
     * @api
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
        } elseif ($this->hasAttribute('receiver')) {
            $controlId = $this->view->getUniqueId($this->getAttribute('receiver'));
            $attributes['id'] = $controlId;
        } else {
            $controlId = $this->view->getUniqueId($name);
            $attributes['id'] = $controlId;
        }

        $wrapperClass = 'labeled-control';
        if ($this->hasAttribute('labelWrapClass')) {
            $wrapperClass = $this->getAttribute('labelWrapClass');
        }
        $content = '';

        if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::LABEL_NONE) {
            $content .= $this->controlTag($tag, $name, $flags, $cssClass, $attributes, $tagContent);
        } else {

            if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::LABEL_RIGHT) {
                $wrapperClass .= ' label-right';
                $content .= $this->controlTag($tag, $name, $flags, $cssClass, $attributes, $tagContent);
                $content .= $this->label($label, $controlId);
            } else {
                if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::LABEL_ABOVE) {
                    $wrapperClass .= ' label-above';
                } elseif ($flags & \Nethgui\Renderer\WidgetFactoryInterface::LABEL_LEFT) {
                    $wrapperClass .= ' label-left';
                }
                $content .= $this->label($label, $controlId);
                $content .= $this->controlTag($tag, $name, $flags, $cssClass, $attributes, $tagContent);
            }

            $wrapTag = $this->getAttribute('labelWrapTag', 'div');
            if ($wrapTag) {
                $content = $this->openTag($wrapTag, array('class' => $wrapperClass)) . $content . $this->closeTag($wrapTag);
            }
        }

        return $content;
    }

    /**
     * Generate XHTML markup for a FORM control, such as INPUT, BUTTON, TEXTAREA
     *
     * @api
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
                $cssClass .= ' keepdisabled';
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

    /**
     * If no LABEL_* bit is set, apply $default bit
     * 
     * @param int $flags The input bits
     * @param int $default The default LABEL bit
     * @return type The output bits
     */
    protected function applyDefaultLabelAlignment($flags, $default)
    {
        return (\Nethgui\Renderer\WidgetFactoryInterface::LABEL_NONE | \Nethgui\Renderer\WidgetFactoryInterface::LABEL_ABOVE | \Nethgui\Renderer\WidgetFactoryInterface::LABEL_LEFT | \Nethgui\Renderer\WidgetFactoryInterface::LABEL_RIGHT) & $flags ? $flags : $flags | $default;
    }

    /**
     * Get an XHTML opening tag string
     *
     * @api
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
     * @api
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
     * @api
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
     * @return string The XHTML formatted attributes
     */
    private function prepareXhtmlAttributes($attributes)
    {
        $content = '';

        foreach ($attributes as $attribute => $value) {
            if ($value === FALSE) {
                continue;
            }
            $content .= $attribute . '=\'' . htmlspecialchars($value, ENT_QUOTES) . '\' ';
        }

        return ' ' . trim($content);
    }

    /**
     * Generate a control name for the given $parts. If no parts are given
     * the name is generated from the module referenced by the view.
     * 
     * @api
     * @param string $parts
     * @return string
     */
    protected function getControlName($parts = '')
    {
        $nameSegments = $this->view->resolvePath($parts);
        $prefix = array_shift($nameSegments); // the first segment is not wrapped into square brackets
        return $prefix . '[' . implode('][', $nameSegments) . ']';
    }

    /**
     * Get the js types required by this widget class.
     * 
     * Each element of the returned array is a string <namespace>:<widgetType>
     * 
     * @api
     * @return array An array of required js widget types
     */
    protected function getJsWidgetTypes()
    {
        return array(
            'Nethgui:' . strtolower(\Nethgui\array_end(explode('\\', get_class($this))))
        );
    }

    public function render()
    {
        /*
         * Refs #620
         * Client commands are applied to the appointed widget to keep the view consistent
         * in both CLIENT and SERVER modes.
         */
        $this->invokeCommands();
        $content = parent::render();

        if ($this->canEscapeUnobstrusive($this->getAttribute('flags', 0))) {
            $content = $this->escapeUnobstrusive($content);
        }

        if (NETHGUI_ENABLE_INCLUDE_WIDGET) {
            $types = array_merge(array('Nethgui:base'), $this->getJsWidgetTypes());
            foreach ($types as $type) {
                $typeParts = explode(':', $type);
                $this->getRenderer()->includeFile(sprintf('%s/Js/jquery.nethgui.%s.js', $typeParts[0], $typeParts[1]));
            }
        }

        return $content;
    }

    /**
     *
     * @deprecated since version 1.6
     */
    private function invokeCommands()
    {
        if ( ! $this->hasAttribute('receiver')) {
            return;
        }

        $targetName = $this->getAttribute('receiver');

        if ( ! $this->view->hasCommandList($targetName)) {
            return;
        }

        $command = $this->view->getCommandList($targetName);

        if ( ! $command->isExecuted()) {
            $command->setReceiver($this)->execute();
        }
    }

    public function executeCommand(\Nethgui\View\ViewInterface $origin, $selector, $name, $arguments)
    {
        $this->getLog()->deprecated(sprintf("%%s %%s: %s() command is DEPRECATED on Xhtml widget!", __CLASS__, $name));
        if ($name === 'requireFlag') {
            $flags = intval($arguments[0]);
            $this->setAttribute('flags', $flags | $this->getAttribute('flags', 0));
            return;
        } elseif ($name === 'rejectFlag') {
            $flags = ~intval($arguments[0]);
            $this->setAttribute('flags', $flags & $this->getAttribute('flags', 0));
        } elseif ($name === 'enable') {
            $this->executeCommand($origin, $selector, 'rejectFlag', array(\Nethgui\Renderer\WidgetFactoryInterface::STATE_DISABLED));
        } elseif ($name === 'disable') {
            $this->executeCommand($origin, $selector, 'requireFlag', array(\Nethgui\Renderer\WidgetFactoryInterface::STATE_DISABLED));
        }
    }

    /**
     * Get a closure that provides a default value for an attribute
     *
     * @param string $message
     * @param array $args
     * @return closure
     */
    protected function getTranslateClosure($message, $args = array())
    {
        $view = $this->view;
        $f = function($attributeName) use ($view, $message, $args) {
            return $view->translate($message, $args);
        };
        return $f;
    }

    protected function escapeUnobstrusive($content)
    {
        return "<script class='unobstrusive'>/*<![CDATA[*/\ndocument.write(" . json_encode(strval($content)) . ");\n/*]]>*/</script>";
    }

    protected function canEscapeUnobstrusive($flags)
    {
        $unobstrusiveRequired = ($flags & \Nethgui\Renderer\WidgetFactoryInterface::STATE_UNOBTRUSIVE) !== 0;
        $unobstrusiveApplying = ($this->getRenderer()->getDefaultFlags() & \Nethgui\Renderer\WidgetFactoryInterface::STATE_UNOBTRUSIVE) === 0;
        return $unobstrusiveRequired && $unobstrusiveApplying;
    }

    public function insertPlugins($name = 'Plugin')
    {
        $pluginList = array();

        if (empty($this->view[$name])) {
            return $this;
        }

        foreach ($this->view[$name] as $pluginView) {
            if ($pluginView instanceof \Nethgui\View\ViewInterface) {
                $pluginModule = $pluginView->getModule();

                $cat = $pluginModule->getAttributesProvider()->getCategory();

                if ( ! isset($pluginList[$cat])) {
                    // add a panel for the new Category:
                    $pluginList[$cat] = $this->view->panel()
                        ->setAttribute('name', $cat)
                        ->setAttribute('title', $pluginView->translate($cat . '_Title'))
                        ->setAttribute('isPluginPlaceholder', TRUE)
                    ;
                }

                $pluginLiteral = $this->view->literal($pluginView);
                $pluginLiteral->setAttribute('isPlugin', TRUE);

                // add plugin view to the Category
                $pluginList[$cat]->insert($pluginLiteral);
            } else {
                $this->insert($this->view->literal($pluginView)); // add a new element
            }
        }

        ksort($pluginList);
        foreach ($pluginList as $plugin) {
            $this->insert($plugin);
        }

        return $this;
    }

    /**
     * Return a string of OPTGROUP and OPTION tags.
     * 
     * @param string $value The "selected" value
     * @param array The options and groups of options
     * @return string 
     * 
     * @see redmine #348
     */
    protected function optGroups($value, $choices)
    {
        $tagContent = '';

        if ( ! is_array($choices)) {
            throw new \InvalidArgumentException(sprintf('%s: invalid choices type (%s). Must be an array', __CLASS__, gettype($choices)), 1340631080);
        }

        foreach (array_values($choices) as $choice) {
            $labelText = ! empty($choice[1]) ? $choice[1] : htmlspecialchars(strval($choice[0]));
            if (is_array($choice[0])) {
                // nested options => create optgroup
                $tagContent .= $this->openTag('optgroup', array('label' => $labelText));
                $tagContent .= $this->optGroups($value, $choice[0]);
                $tagContent .= $this->closeTag('optgroup');
            } else {
                $tagContent .= $this->openTag('option', array('value' => $choice[0], 'selected' => ($value == $choice[0] ? 'selected' : FALSE)));
                $tagContent .= $labelText;
                $tagContent .= $this->closeTag('option');
            }
        }

        return $tagContent;
    }

    public static function hashToDatasource($H, $sort = FALSE)
    {
        $D = array();

        if ( ! is_array($H) && ! $H instanceof \Traversable) {
            return $D;
        }

        foreach ($H as $k => $v) {
            if (is_array($v)) {
                $D[] = array(self::hashToDatasource($v, $sort), $k);
            } elseif (is_string($v)) {
                $D[] = array($k, $v);
            }
        }

        if ($sort === TRUE) {
            usort($D, function($a, $b) {
                return strcasecmp($a[1], $b[1]);
            });
        }

        return $D;
    }

    /**
     * Construct the choices
     * 
     * @param string $name Name of the view member where the currently selected value is stored
     * @param string &$dataSourceName Output parameter, where the effective datasource name is stored
     * @return array The datasource choices.
     * @api
     */
    protected function getChoices($name, &$dataSourceName)
    {
        $choices = $this->getAttribute('choices', $name . 'Datasource');

        if (is_string($choices)) {
            // Get the choices from the view member
            $dataSourceName = $choices;
            if (isset($this->view[$dataSourceName])) {
                $choices = $this->view[$dataSourceName];
            }
        } else {
            // The data source name is the selector name with 'Datasource' suffix.
            $dataSourceName = $name . 'Datasource';
        }

        if ($choices instanceof \Traversable) {
            $choices = iterator_to_array($choices);
        } elseif ( ! is_array($choices)) {
            $choices = array();
        }

        return $choices;
    }

}