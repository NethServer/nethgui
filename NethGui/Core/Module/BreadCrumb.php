<?php
/**
 * @package Core
 * @subpackage Module
 */

/**
 * @package Core
 * @subpackage Module
 */
class NethGui_Core_Module_BreadCrumb extends NethGui_Core_Module_Abstract
{

    /**
     *
     * @var NethGui_Core_ModuleInterface
     */
    private $currentModule;
    /**
     * @var NethGui_Core_ModuleSetInterface
     */
    private $moduleSet;

    public function __construct(NethGui_Core_ModuleSetInterface $moduleSet, $currentModuleIdentifier)
    {
        parent::__construct();
        $this->moduleSet = $moduleSet;
        $this->viewTemplate = array($this, 'renderBreadcrumbMenu');
        $this->currentModule = $this->moduleSet->findModule($currentModuleIdentifier);
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

}