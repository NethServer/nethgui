<?php
/**
 * @package Module
 */

/**
 * @package Module
 */
class Nethgui_Module_Menu extends Nethgui_Core_Module_Abstract
{

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
     * TODO
     * @param RecursiveIterator $rootModule
     * @return string
     */
    private function iteratorToHtml(RecursiveIterator $menuIterator, $level = 0)
    {
        if ($level === 0) {
            $allWrap = '%s';
            $itemWrap = '%s';
        } elseif ($level > 4) {
            return '';
        } else {
            $allWrap = '<ul>%s</ul>';
            $itemWrap = '<li>%s</li>';
        }



        $framework = Nethgui_Framework::getInstance();

        $output = '';

        $menuIterator->rewind();

        while ($menuIterator->valid()) {
            $item = $framework->renderModuleAnchor($menuIterator->current());

            if ($menuIterator->hasChildren()) {
                $item .= $this->iteratorToHtml($menuIterator->getChildren(), $level + 1);
            }

            $output .= sprintf($itemWrap, $item);

            $menuIterator->next();
        }

        return sprintf($allWrap, $output);
    }

    public function renderModuleMenu($view)
    {
        return $this->iteratorToHtml($this->menuIterator);
    }

    public function prepareView(Nethgui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);

        if ($mode === self::VIEW_SERVER) {
            $view->setTemplate(array($this, 'renderModuleMenu'));
        }
    }

}