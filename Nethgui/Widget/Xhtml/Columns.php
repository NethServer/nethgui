<?php
/**
 * @package Widget
 * @subpackage Xhtml
 * @author Davide Principi <davide.principi@nethesis.it>
 * @internal
 * @ignore
 */

/**
 *
 * @package Widget
 * @subpackage Xhtml
 * @internal
 * @ignore
 */
class Nethgui_Widget_Xhtml_Columns extends Nethgui_Widget_Xhtml_Panel
{
    public function render()
    {
        $childCountClass = ' c' . count($this->getChildren());
        
        $this->setAttribute('class', $this->getAttribute('class', 'columns') . $childCountClass);

        return parent::render();
    }

    protected function wrapChild($childOutput)
    {
        return '<div class="column">' . $childOutput . '</div>';
    }
}