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
class NethGui_Widget_Xhtml_Dialog extends NethGui_Widget_Xhtml
{

    public function render()
    {
        $name = $this->getParameter('name');
        $value = $this->getParameter('value');
        $flags = $this->getParameter('flags');
        $content = '';

        $className = 'dialog';

        if ($flags & self::DIALOG_SUCCESS) {
            $className .= ' success';
        } elseif ($flags & self::DIALOG_WARNING) {
            $className .= ' warning';
        } elseif ($flags & self::DIALOG_ERROR) {
            $className .= ' error';
        }

        if ($flags & self::DIALOG_EMBEDDED) {
            $className .= ' embedded';
            // unset the EMBEDDED flag:
            $flags ^= self::DIALOG_EMBEDDED;
        } elseif ($flags & self::DIALOG_MODAL) {
            $className .= ' modal';
            // unset the MODAL flag:
            $flags ^= self::DIALOG_MODAL;
        } else {
            $className .= ' embedded'; // default dialog class
        }

        if ($flags & self::STATE_DISABLED) {
            $className .= ' disabled';
        }

        $attributes = array(
            'class' => $className,
            'id' => $identifier,
        );

        $content .= $this->openTag('div', $attributes);
        $content .= $this->renderChildren();
        $content .= $this->closeTag('div');

        return $content;
    }

}