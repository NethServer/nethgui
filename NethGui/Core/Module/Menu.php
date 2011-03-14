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
    private function renderModuleMenu(RecursiveIterator $menuIterator, $level = 0)
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
                $output .= $this->renderModuleMenu($menuIterator->getChildren(), $level + 1);
            }

            $output .= '</li>';

            $menuIterator->next();
        }

        return '<ul>' . $output . '</ul>';
    }

    public function prepareView(NethGui_Core_ViewInterface $response)
    {
        $this->parameters['html'] = $this->renderModuleMenu($this->menuIterator);
        parent::prepareView($response);
    }
}