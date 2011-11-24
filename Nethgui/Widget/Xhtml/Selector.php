<?php
/**
 * @author Davide Principi <davide.principi@nethesis.it>
 * @internal
 * @ignore
 */

namespace Nethgui\Widget\Xhtml;

/**
 *
 * @internal
 * @ignore
 */
class Selector extends \Nethgui\Widget\XhtmlWidget
{

    public function render()
    {
        $name = $this->getAttribute('name');
        $flags = $this->getAttribute('flags');
        $choices = $this->getAttribute('choices', $name . 'Datasource');
        $value = $this->view[$name];

        if ($value instanceof \Traversable) {
            $value = iterator_to_array($value);
        }

        if (is_null($value)) {
            if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::SELECTOR_MULTIPLE) {
                $value = array();
            } else {
                $value = '';
            }
        }

        if (is_string($choices)) {
            // Get the choices from the view member
            $dataSourceName = $choices;
            $choices = $this->view[$dataSourceName];
        } else {
            // The data source name is the selector name with 'Datasource' suffix.
            $dataSourceName = $name . 'Datasource';
        }

        if ($choices instanceof \Traversable) {
            $choices = iterator_to_array($this->view[$choices]);
        } elseif ( ! is_array($choices)) {
            $choices = array();
        }

        $cssClass = 'Selector '
            . ($flags & \Nethgui\Renderer\WidgetFactoryInterface::SELECTOR_MULTIPLE ? 'multiple ' : '')
            . ' '
            . $this->getAttribute('class')
            . ' '
            . $this->getClientEventTarget()
            . ' '
            . $this->view->getClientEventTarget($dataSourceName);
        ;

        // Render the choices list
        $attributes = array('class' => $cssClass, 'id' => $this->view->getUniqueId($name));

        if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::SELECTOR_DROPDOWN) {
            return $this->renderDropdown($value, $choices, $attributes);
        } else {
            return $this->renderWidgetList($value, $choices, $attributes);
        }
    }

    private function renderDropdown($value, $choices, $attributes)
    {
        $name = $this->getAttribute('name');
        $flags = $this->getAttribute('flags');
        $label = $this->getAttribute('label', $name . '_label');
        $flags = $this->applyDefaultLabelAlignment($flags, \Nethgui\Renderer\WidgetFactoryInterface::LABEL_ABOVE);
        if (count($choices) == 0) {
            $tagContent = '<option selected="selected" value=""/>';
        } else {
            $tagContent = $this->generateSelectorContentDropdown($name, $value, $choices, $flags);
        }
        $content = $this->labeledControlTag($label, 'select', $name, $flags, '', $attributes, $tagContent);
        return $content;
    }

    private function renderWidgetList($value, $choices, $attributes)
    {
        $name = $this->getAttribute('name');
        $flags = $this->getAttribute('flags');

        $hiddenWidget = new Hidden($this->view);
        $hiddenWidget->setAttribute('flags', $flags)
            ->setAttribute('value', '')
            ->setAttribute('class', 'Hidden')
            ->setAttribute('name', $name);

        $contentWidget = new Literal($this->view);
        $contentWidget->setAttribute('data', $this->generateSelectorContentWidgetList($name, $value, $choices, $flags));

        $panelWidget = new Panel($this->view);
        $panelWidget
            ->setAttribute('class', $attributes['class'])
            ->setAttribute('name', $name)
            ->insert($hiddenWidget)
            ->insert($contentWidget);

        $fieldsetWidget = new Fieldset($this->view);
        $fieldsetWidget->setAttribute('template', $this->getAttribute('label', $name . '_label'))
            ->setAttribute('flags', $this->getAttribute('flags'));
        if ($this->hasAttribute('icon-before')) {
            $fieldsetWidget->setAttribute('icon-before', $this->getAttribute('icon-before'));
        }
        $fieldsetWidget->insert($panelWidget);
        return $fieldsetWidget->render();
    }

    /**
     *
     * @param string $name
     * @param array|string $value
     * @param array $choices
     * @param integer $flags
     */
    private function generateSelectorContentWidgetList($name, $value, $choices, $flags)
    {
        $content = '';

        if (count($choices) == 0) {
            return '';
        }

        $content .= $this->openTag('ul');
        foreach (array_values($choices) as $index => $choice) {

            $content .= $this->openTag('li');
            $choiceFlags = ($flags & ~(\Nethgui\Renderer\WidgetFactoryInterface::LABEL_LEFT | \Nethgui\Renderer\WidgetFactoryInterface::LABEL_NONE))  | \Nethgui\Renderer\WidgetFactoryInterface::LABEL_RIGHT;

            if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::SELECTOR_MULTIPLE) {
                $choiceName = $name . '/' . $index;

                if (in_array($choice[0], $value)) {
                    $choiceFlags |= \Nethgui\Renderer\WidgetFactoryInterface::STATE_CHECKED;
                }

                $attributes = array(
                    'type' => 'checkbox',
                    'value' => $choice[0],
                );
            } else {
                $choiceName = $name;

                if ($choice[0] == $value) {
                    $choiceFlags |= \Nethgui\Renderer\WidgetFactoryInterface::STATE_CHECKED;
                }

                $attributes = array(
                    'type' => 'radio',
                    'value' => $choice[0],
                    'id' => $this->view->getUniqueId($name . '/' . $index),
                );
            }

            $choiceLabel = ( ! empty($choice[1]) ? $choice[1] : $choice[0]);
            $content .= $this->labeledControlTag($choiceLabel, 'input', $choiceName, $choiceFlags, 'choice', $attributes);
            $content .= $this->closeTag('li');
        }
        $content .= $this->closeTag('ul');

        return $content;
    }

    /**
     * Dropdown list layout
     * 
     * @see redmine #348
     */
    private function generateSelectorContentDropdown($name, $value, $choices, $flags)
    {
        $tagContent = '';

        foreach (array_values($choices) as $index => $choice) {
            $labelText = ! empty($choice[1]) ? $choice[1] : htmlspecialchars(strval($choice[0]));
            if (is_array($choice[0])) {
                // nested options => create optgroup
                $tagContent .= $this->openTag('optgroup', array('label' => $labelText));
                $tagContent .= $this->generateSelectorContentDropdown($name, $value, $choice[0], $flags);
                $tagContent .= $this->closeTag('optgroup');
            } else {
                $tagContent .= $this->openTag('option', array('value' => $choice[0], 'selected' => $value == $choice[0] ? 'selected' : FALSE));
                $tagContent .= $labelText;
                $tagContent .= $this->closeTag('option');
            }
        }

        return $tagContent;
    }

}

