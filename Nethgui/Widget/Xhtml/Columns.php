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
class \Nethgui\Widget\Xhtml_Columns extends \Nethgui\Widget\Xhtml_Panel
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
