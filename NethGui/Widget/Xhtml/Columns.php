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
class NethGui_Widget_Xhtml_Columns extends NethGui_Widget_Xhtml_Panel
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