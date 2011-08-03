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
class NethGui_Widget_Xhtml_Dialog extends NethGui_Widget_Xhtml_Inset
{

    public function render()
    {
        $flags = $this->getAttribute('flags');
        $content = '';
        $className = 'dialog';

        if ($flags & NethGui_Renderer_Abstract::DIALOG_SUCCESS) {
            $className .= ' success';
        } elseif ($flags & NethGui_Renderer_Abstract::DIALOG_WARNING) {
            $className .= ' warning';
        } elseif ($flags & NethGui_Renderer_Abstract::DIALOG_ERROR) {
            $className .= ' error';
        }

        if ($flags & NethGui_Renderer_Abstract::DIALOG_EMBEDDED) {
            $className .= ' embedded';
            // unset the EMBEDDED flag:
            $flags ^= NethGui_Renderer_Abstract::DIALOG_EMBEDDED;
        } elseif ($flags & NethGui_Renderer_Abstract::DIALOG_MODAL) {
            $className .= ' modal';
            // unset the MODAL flag:
            $flags ^= NethGui_Renderer_Abstract::DIALOG_MODAL;
        } else {
            $className .= ' embedded'; // default dialog class
        }

        if ($flags & NethGui_Renderer_Abstract::STATE_DISABLED) {
            $className .= ' disabled';
        }

        if ($this->hasAttribute('name')) {
            $id = $this->view->getUniqueId($this->getAttribute('name'));
        } else {
            $id = FALSE;
        }

        $attributes = array(
            'class' => $className,
            'id' => $id
        );

        $content .= $this->openTag('div', $attributes);
        $content .= parent::render();
        $content .= $this->closeTag('div');

        return $content;
    }

}