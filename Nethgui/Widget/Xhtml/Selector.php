<?php
namespace Nethgui\Widget\Xhtml;

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
 *
 */
class Selector extends \Nethgui\Widget\XhtmlWidget
{

    protected function renderContent()
    {
        $name = $this->getAttribute('name');
        $flags = $this->getAttribute('flags');
        
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
        
        $choices = $this->getChoices($name, $dataSourceName);

        $cssClass = 'Selector '
            . ($flags & \Nethgui\Renderer\WidgetFactoryInterface::SELECTOR_MULTIPLE ? 'multiple ' : '')
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
        $label = $this->getAttribute('label', $this->getTranslateClosure($name . '_label'));
        $flags = $this->applyDefaultLabelAlignment($flags, \Nethgui\Renderer\WidgetFactoryInterface::LABEL_ABOVE);
        if (count($choices) == 0) {
            $tagContent = '<option selected="selected" value=""/>';
        } else {
            $tagContent = $this->optGroups($name, $value, $choices);
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
        $fieldsetWidget->setAttribute('template', $this->getAttribute('label', $this->getTranslateClosure($name . '_label')))
            ->setAttribute('flags', $this->getAttribute('flags'));
        if ($this->hasAttribute('icon-before')) {
            $fieldsetWidget->setAttribute('icon-before', $this->getAttribute('icon-before'));
        }
        $fieldsetWidget->insert($panelWidget);
        return $fieldsetWidget->renderContent();
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

}

