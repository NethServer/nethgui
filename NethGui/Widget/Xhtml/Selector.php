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
        $name = $this->getParameter('name');
        $flags = $this->getParameter('flags');
        $choices = $this->getParameter('choices');
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

        $selectorModeIsDefined = (self::SELECTOR_MULTIPLE | self::SELECTOR_SINGLE) & $flags;

        if ($value instanceof Traversable) {
            $value = iterator_to_array($value);
        } elseif (is_null($value) && $selectorModeIsDefined) {
            if ($flags & self::SELECTOR_MULTIPLE) {
                $value = array();
            } else {
                $value = '';
            }
        }

        if ( ! $selectorModeIsDefined) {
            if (is_array($value)) {
                $flags |= self::SELECTOR_MULTIPLE;
            } else {
                $flags |= self::SELECTOR_SINGLE;
            }
        }

        $fieldsetAttributes = array(
            'class' => 'selector ' . is_array($value) ? 'multiple' : 'single',
            'id' => $this->getUniqueId($name)
        );

        $content .= $this->openTag('fieldset', $fieldsetAttributes);
        $content .= $this->openTag('legend');
        $content .= htmlspecialchars($this->translate($name . '_label'));
        $content .= $this->closeTag('legend');

        $choicesAttributes = array(
            'class' => 'choices',
            'id' => $this->getUniqueId($dataSourceName)
        );

        $content .= $this->openTag('div', $choicesAttributes);

        $hidden = new NethGui_Widget_Xhtml_Hidden($this->view);
        $hidden
            ->setAttribute('name', $name)
            ->setAttribute('flags', $flags)
            ->setAttribute('value', '');
        ;

        $content .= $hidden->render();

        $selectorEnabled = ! ($flags & self::STATE_DISABLED);

        if ($selectorEnabled && count($choices) > 0) {
            $this->generateSelectorContent($name, $value, $choices, $flags);
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
            $choiceFlags = $flags;

            if ($flags & self::SELECTOR_MULTIPLE) {
                $choiceName = $name . '/' . $index;
                $choiceId = $choiceName;

                if (in_array($choice[0], $value)) {
                    $choiceFlags |= self::STATE_CHECKED;
                }

                $attributes = array(
                    'type' => 'checkbox',
                    'value' => $choice[0],
                );
            } elseif ($flags & self::SELECTOR_SINGLE) {
                $choiceName = $name;
                $choiceId = $name . '/' . $index;

                if ($choice[0] == $value) {
                    $choiceFlags |= self::STATE_CHECKED;
                }

                $attributes = array(
                    'type' => 'radio',
                    'value' => $choice[0],
                    'id' => $this->view->getUniqueId($choiceId),
                );
            }

            $this->controlTag('input', $choiceName, $choiceFlags, '', $attributes);
            $content .= $this->openTag('label', array('for' => $this->getUniqueId($choiceId)));
            $this->append( ! empty($choice[1]) ? $choice[1] : $choice[0]);
            $content .= $this->closeTag('label');

            $content .= $this->closeTag('li');
        }
        $content .= $this->closeTag('ul');

        return $content;
    }

}

