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
class FieldsetSwitch extends \Nethgui\Widget\XhtmlWidget
{

    public function render()
    {
        $name = $this->getAttribute('name');
        $value = $this->getAttribute('value');
        $flags = $this->getAttribute('flags');
        $content = '';

        $content .= $this->openTag('div', array('class' => 'FieldsetSwitch'));

        $chooser = new RadioButton($this->view);
        $chooser
            ->setAttribute('name', $name)
            ->setAttribute('value', $value)
            ->setAttribute('flags', $flags)
        ;

        $content .= $chooser->render();
        $content .= $this->openTag('fieldset', array('class' => 'FieldsetSwitchPanel'));
        $content .= $this->renderChildren();        
        $content .= $this->closeTag('fieldset');
        $content .= $this->closeTag('div');
        
        return $content;
    }

}
