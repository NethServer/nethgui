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
class NethGui_Widget_Xhtml_Fieldset extends NethGui_Widget_Xhtml_Text
{

    public function render()
    {
        if ($this->hasAttribute('name')) {
            $text = parent::render();
        } else {
            $text = '';
        }

        $content = '';
        $content .= $this->opentag('fieldset');

        if ($text) {
            $content .= $this->opentag('legend');
            $content .= $text;
            $content .= $this->closetag('legend');
        }

        $content .= $this->renderChildren();
        $content .= $this->closetag('fieldset');

        return $content;
    }

}