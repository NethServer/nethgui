<?php
/**
 * @package Widget
 * @subpackage Xhtml
 * @author Davide Principi <davide.principi@nethesis.it>
 * @internal
 */

/**
 *
 * @package Widget
 * @subpackage Xhtml
 * @internal
 */
class NethGui_Widget_Xhtml_Selector extends NethGui_Widget_Xhtml
{

    public function render()
    {
        $name = $this->getAttribute('name');
        $flags = $this->getAttribute('flags');
        $choices = $this->getAttribute('choices', $name . 'Datasource');
        $value = $this->view[$name];
        $content = '';

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

        $selectorModeIsDefined = (NethGui_Renderer_Abstract::SELECTOR_MULTIPLE | NethGui_Renderer_Abstract::SELECTOR_SINGLE) & $flags;

        if ($value instanceof Traversable) {
            $value = iterator_to_array($value);
        } elseif (is_null($value) && $selectorModeIsDefined) {
            if ($flags & NethGui_Renderer_Abstract::SELECTOR_MULTIPLE) {
                $value = array();
            } else {
                $value = '';
            }
        }

        if ( ! $selectorModeIsDefined) {
            if (is_array($value)) {
                $flags |= NethGui_Renderer_Abstract::SELECTOR_MULTIPLE;
            } else {
                $flags |= NethGui_Renderer_Abstract::SELECTOR_SINGLE;
            }
        }

        $fieldsetAttributes = array(
            'class' => 'selector ' . (is_array($value) ? 'multiple' : 'single'),
            'id' => $this->view->getUniqueId($name)
        );

        $content .= $this->openTag('fieldset', $fieldsetAttributes);
        $content .= $this->openTag('legend');
        $content .= htmlspecialchars($this->view->translate($name . '_label'));
        $content .= $this->closeTag('legend');

        $choicesAttributes = array(
            'class' => 'choices',
            'id' => $this->view->getUniqueId($dataSourceName)
        );

        $content .= $this->openTag('div', $choicesAttributes);
        
        $content .= $this->controlTag('input', $name, $flags, '', array('type'=>'hidden'));

        $selectorEnabled = ! ($flags & NethGui_Renderer_Abstract::STATE_DISABLED);

        if ($selectorEnabled && count($choices) > 0) {
            $content .= $this->generateSelectorContent($name, $value, $choices, $flags);
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
    private function generateSelectorContent($name, $value, $choices, $flags)
    {
        $content = '';

        $content .= $this->openTag('ul');
        foreach (array_values($choices) as $index => $choice) {

            $content .= $this->openTag('li', array('class' => 'labeled-control label-right'));
            $choiceFlags = $flags & ~NethGui_Renderer_Abstract::LABEL_RIGHT | NethGui_Renderer_Abstract::LABEL_RIGHT;

            if ($flags & NethGui_Renderer_Abstract::SELECTOR_MULTIPLE) {
                $choiceName = $name . '/' . $index;
                $choiceId = $choiceName;

                if (in_array($choice[0], $value)) {
                    $choiceFlags |= NethGui_Renderer_Abstract::STATE_CHECKED;
                }

                $attributes = array(
                    'type' => 'checkbox',
                    'value' => $choice[0],
                );
            } elseif ($flags & NethGui_Renderer_Abstract::SELECTOR_SINGLE) {
                $choiceName = $name;
                $choiceId = $name . '/' . $index;

                if ($choice[0] == $value) {
                    $choiceFlags |= NethGui_Renderer_Abstract::STATE_CHECKED;
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

}

