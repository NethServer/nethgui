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
class NethGui_Widget_Xhtml_Fieldset extends NethGui_Widget_Xhtml_TextLabel
{

    public function render()
    {
        $this->setAttribute('tag', 'legend');

        if ($this->hasAttribute('name')) {
            $text = parent::render();
        } else {
            $text = '';
        }

        $content = '';
        $content .= $this->opentag('fieldset');

        if ($text) {
            $content .= $text;
        }

        $content .= $this->renderChildren();
        $content .= $this->closetag('fieldset');

        return $content;
    }

}