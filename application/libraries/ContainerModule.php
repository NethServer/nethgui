<?php
/**
 * A ContainerModule wraps its children into a DIV tag.
 */
class ContainerModule extends StandardModuleComposite {
    protected function decorate($output)
    {
        return '<div class="'. $this->getIdentifier() .'">' . $output . '</div>';
    }
}
?>
