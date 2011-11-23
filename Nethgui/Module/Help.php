<?php
/**
 * @package Module
 */

namespace Nethgui\Module;

/**
 * @package Module
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class Help extends \Nethgui\Core\Module\Controller
{

    /**
     *
     * @var \Nethgui\Core\ModuleSetInterface
     */
    private $moduleSet;

    public function __construct(\Nethgui\Core\ModuleSetInterface $moduleSet)
    {
        parent::__construct(NULL);
        $this->moduleSet = $moduleSet;
    }

    public function initialize()
    {
        parent::initialize();
        $this->loadChildren(array('Show', 'Template', 'Read'));

        // Propagate moduleSet to children
        foreach ($this->getChildren() as $child) {
            $child->moduleSet = $this->moduleSet;
        }
    }

    public function prepareView(\Nethgui\Core\ViewInterface $view, $mode)
    {
        if (is_null($this->currentAction)) {
            $view->setTemplate('Nethgui\Template\Help');
        } else {
            parent::prepareView($view, $mode);
        }
    }

}
