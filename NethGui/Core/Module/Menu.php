<?php

final class NethGui_Core_Module_Menu extends NethGui_Core_Module_Standard {

    /**
     *
     * @var RecursiveIterator
     */
    private $menuIterator;

    public function __construct(RecursiveIterator $menuIterator)
    {
        parent::__construct();
        $this->menuIterator = $menuIterator;
    }

    /**
     *
     * @param RecursiveIterator $rootModule
     * @return string
     */
    private function iteratorToHtml(RecursiveIterator $menuIterator, $level = 0)
    {
        if ($level > 4) {
            return '';
        }

        $framework = NethGui_Framework::getInstance();

        $output = '';

        $menuIterator->rewind();

        while ($menuIterator->valid()) {
            $output .= '<li><div class="moduleTitle">' . $framework->renderModuleAnchor($menuIterator->current()) . '</div>';

            if ($menuIterator->hasChildren()) {
                $output .= $this->iteratorToHtml($menuIterator->getChildren(), $level + 1);
            }

            $output .= '</li>';

            $menuIterator->next();
        }

        return '<ul>' . $output . '</ul>';
    }

    public function renderModuleMenu($viewState) {       
        return $this->iteratorToHtml($this->menuIterator);
    }

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        $view->setTemplate(array($this, 'renderModuleMenu'));
    }
}