<?php
/**
 * @package Widget
 * @subpackage Xhtml
 * @author Davide Principi <davide.principi@nethesis.it>
 * @internal
 * @ignore
 */

/**
 * Attributes:
 *
 * - name, see TextLabel
 * - template, see TextLabel
 * - flags
 *
 * @package Widget
 * @subpackage Xhtml
 * @internal
 * @ignore
 */
class Nethgui_Widget_Xhtml_Fieldset extends Nethgui_Widget_Xhtml_Panel
{

    public function render()
    {
        // force container tag to FIELDSET:
        $this->setAttribute('tag', 'fieldset');

        if ($this->getAttribute('flags') & Nethgui_Renderer_Abstract::FIELDSET_EXPANDABLE) {
            $this->setAttribute('class', 'Fieldset expandable');
        } else {
            $this->setAttribute('class', 'Fieldset');
        }

        $legendWidget = new Nethgui_Widget_Xhtml_TextLabel($this->view);
        $legendWidget->setAttribute('tag', 'legend');
        $renderLegend = FALSE;

        if ($this->hasAttribute('name')) {
            $legendWidget->setAttribute('name', $this->getAttribute('name'));
            $renderLegend = TRUE;
        }

        if ($this->hasAttribute('template')) {
            $legendWidget->setAttribute('template', $this->getAttribute('template'));
            $renderLegend = TRUE;
        }

        if ($renderLegend) {
            $legendWidget->setAttribute('icon-before', $this->getAttribute('icon-before', FALSE));
            $this->prepend($legendWidget);
        }

        return parent::render();
    }

}