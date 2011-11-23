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
class Nethgui\Widget\Xhtml_Fieldset extends Nethgui\Widget\Xhtml_Panel
{

    public function render()
    {
        // force container tag to FIELDSET:
        $this->setAttribute('tag', 'fieldset');
        $flags = $this->getAttribute('flags', 0);

        if ($flags & Nethgui\Renderer\WidgetFactoryInterface::FIELDSET_EXPANDABLE) {
            $this->setAttribute('class', 'Fieldset expandable');
        } else {
            $this->setAttribute('class', 'Fieldset');
        }

        $legendWidget = new Nethgui\Widget\Xhtml_TextLabel($this->view);
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

        if ($renderLegend && ! ($flags & Nethgui\Renderer\WidgetFactoryInterface::LABEL_NONE)) {
            $legendWidget->setAttribute('icon-before', $this->getAttribute('icon-before', FALSE));
            $this->prepend($legendWidget);
        }

        return parent::render();
    }

}