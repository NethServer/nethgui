<?php
/**
 * @package Widget
 * @subpackage Xhtml
 * @author Davide Principi <davide.principi@nethesis.it>
 * @internal
 * @ignore
 */

namespace Nethgui\Widget\Xhtml;

/**
 *
 * @package Widget
 * @subpackage Xhtml
 * @internal
 * @ignore
 */
class Nethgui\Widget\Xhtml_ProgressBar extends Nethgui\Widget\Xhtml
{

    public function render()
    {
        $name = $this->getAttribute('name');
        $flags = $this->getAttribute('flags', 0);

        $cssClass = 'Progressbar';

        if ($flags & Nethgui\Renderer\WidgetFactoryInterface::STATE_DISABLED) {
            $cssClass .= ' disabled';
        }

        $cssClass .= ' ' . $this->getClientEventTarget();

        $content = $this->openTag('div', array('class' => $cssClass)) . $this->closeTag('div');
        return $content;
    }

}
