<?php
/**
 * @author Davide Principi <davide.principi@nethesis.it>
 * @internal
 * @ignore
 */

namespace Nethgui\Widget\Xhtml;

/**
 * Attributes:
 *
 * - name, see TextLabel
 * - template, see TextLabel
 * - flags
 *
 * @internal
 * @ignore
 */
class Fieldset extends Panel
{

    public function render()
    {
        // force container tag to FIELDSET:
        $this->setAttribute('tag', 'fieldset');
        $flags = $this->getAttribute('flags', 0);

        if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::FIELDSET_EXPANDABLE) {
            $this->setAttribute('class', 'Fieldset expandable');
        } else {
            $this->setAttribute('class', 'Fieldset');
        }

        $legendWidget = new TextLabel($this->view);
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

        if ($renderLegend && ! ($flags & \Nethgui\Renderer\WidgetFactoryInterface::LABEL_NONE)) {
            $legendWidget->setAttribute('icon-before', $this->getAttribute('icon-before', FALSE));
            $this->prepend($legendWidget);
        }

        return parent::render();
    }

}
