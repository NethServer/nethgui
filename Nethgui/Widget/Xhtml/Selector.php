<?php
/**
 * @package Widget
 * @subpackage Xhtml
 * @author Davide Principi <davide.principi@nethesis.it>
 * @internal
 * @ignore
 */

/**
 *
 * @package Widget
 * @subpackage Xhtml
 * @internal
 * @ignore
 */
class Nethgui_Widget_Xhtml_Selector extends Nethgui_Widget_Xhtml
{

    public function render()
    {
        $name = $this->getAttribute('name');
        $flags = $this->getAttribute('flags');
        $choices = $this->getAttribute('choices', $name . 'Datasource');
        $value = $this->view[$name];

        if ($value instanceof Traversable) {
            $value = iterator_to_array($value);
        }

        if (is_null($value)) {
            if ($flags & Nethgui_Renderer_Abstract::SELECTOR_MULTIPLE) {
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

        if ($choices instanceof Traversable) {
            $choices = iterator_to_array($this->view[$choices]);
        } elseif ( ! is_array($choices)) {
            $choices = array();
        }

        if ($flags & Nethgui_Renderer_Abstract::SELECTOR_DROPDOWN) {
            return $this->renderDropdown($name, $value, $flags, $choices, $dataSourceName);
        } else {
            return $this->renderWidgetList($name, $value, $flags, $choices, $dataSourceName);
        }
    }

    private function renderDropdown($name, $value, $flags, $choices, $dataSourceName)
    {
        $flags = $this->applyDefaultLabelAlignment($flags, Nethgui_Renderer_Abstract::LABEL_ABOVE);
        if ($flags & Nethgui_Renderer_Abstract::STATE_DISABLED) {
            $tagContent = '<option selected="selected" value=""/>';
        } else {
            $tagContent = $this->generateSelectorContentDropdown($name, $value, $choices, $flags);
        }
        $content = $this->labeledControlTag('select', $name, $name, $flags, 'Selector ' . $this->view->getClientEventTarget($dataSourceName), array(), $tagContent);
        return $content;
    }

    private function renderWidgetList($name, $value, $flags, $choices, $dataSourceName)
    {
        $content = '';
        $cssClass = 'Selector ' . ($flags & Nethgui_Renderer_Abstract::SELECTOR_MULTIPLE ? 'multiple ' : '') . $this->getClientEventTarget();

        $fieldsetAttributes = array(
            'class' => $cssClass,
            'id' => $this->view->getUniqueId($name)
        );

        $content .= $this->openTag('fieldset', $fieldsetAttributes);
        $content .= $this->openTag('legend');
        $content .= htmlspecialchars($this->view->translate($name . '_label'));
        $content .= $this->closeTag('legend');

        // Render the choices list

        $choicesAttributes = array(
            'class' => 'choices ' . $this->view->getClientEventTarget($dataSourceName),
            'id' => $this->view->getUniqueId($dataSourceName)
        );

        $selectorEnabled = ! ($flags & Nethgui_Renderer_Abstract::STATE_DISABLED);

        $content .= $this->openTag('div', $choicesAttributes);
        // This hidden control holds the control name prefix:
        $content .= $this->controlTag('input', $name, $flags, '', array('type' => 'hidden'));      
        if ($selectorEnabled && count($choices) > 0) {
            $content .= $this->generateSelectorContentWidgetList($name, $value, $choices, $flags);
        }
        $content .= $this->closeTag('div');
        $content .= $this->closeTag('fieldset');
        return $content;
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

        $content .= $this->openTag('ul');
        foreach (array_values($choices) as $index => $choice) {

            $content .= $this->openTag('li', array('class' => 'labeled-control label-right'));
            $choiceFlags = $flags & ~Nethgui_Renderer_Abstract::LABEL_RIGHT | Nethgui_Renderer_Abstract::LABEL_RIGHT;

            if ($flags & Nethgui_Renderer_Abstract::SELECTOR_MULTIPLE) {
                $choiceName = $name . '/' . $index;
                $choiceId = $choiceName;

                if (in_array($choice[0], $value)) {
                    $choiceFlags |= Nethgui_Renderer_Abstract::STATE_CHECKED;
                }

                $attributes = array(
                    'type' => 'checkbox',
                    'value' => $choice[0],
                );
            } else {
                $choiceName = $name;
                $choiceId = $name . '/' . $index;

                if ($choice[0] == $value) {
                    $choiceFlags |= Nethgui_Renderer_Abstract::STATE_CHECKED;
                }

                $attributes = array(
                    'type' => 'radio',
                    'value' => $choice[0],
                    'id' => $this->view->getUniqueId($choiceId),
                );
            }

            $choiceLabel = ( ! empty($choice[1]) ? $choice[1] : $choice[0]);
            $content .= $this->labeledControlTag('input', $choiceName, $choiceLabel, $choiceFlags, '', $attributes);
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
            $choiceLabel = ( ! empty($choice[1]) ? $choice[1] : $choice[0]);
            $tagContent .= $this->openTag('option', array('value' => $choice[0]));
            $tagContent .= $choiceLabel;
            $tagContent .= $this->closeTag('option');
        }

        return $tagContent;
    }

}

