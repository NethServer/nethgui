<?php
/**
 * @package Core
 * @subpackage Module
 */

/**
 * @package Core
 * @subpackage Module
 */
final class NethGui_Core_Module_BreadCrumb extends NethGui_Core_Module_Standard {
    /**
     *
     * @var NethGui_Core_ModuleInterface
     */
    private $currentModule;
    /**
     * @var NethGui_Core_ModuleSetInterface
     */
    private $moduleSet;

    public function __construct(NethGui_Core_ModuleSetInterface $moduleSet, NethGui_Core_ModuleInterface $currentModule)
    {
        parent::__construct();
        $this->currentModule = $currentModule;
        $this->moduleSet = $moduleSet;
    }

    public function renderBreadcrumbMenu($viewState)
    {
        $module = $this->currentModule;
        $framework = NethGui_Framework::getInstance();

        $rootLine = array();

        while ( ! is_null($module)
        && $module instanceof NethGui_Core_TopModuleInterface
        ) {
            $rootLineElement = $framework->renderModuleAnchor($module);
            if (strlen($rootLineElement) > 0) {
                $rootLine[] = $rootLineElement;
            }
            $module = $this->moduleSet->findModule($module->getParentMenuIdentifier());
        }

        $rootLine = array_reverse($rootLine);

        // TODO: wrap into LI tag.
        return implode(' &gt; ', $rootLine);
    }

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {        
        parent::prepareView($view, $mode);
        $view->setTemplate(array($this, 'renderBreadcrumbMenu'));
    }
}