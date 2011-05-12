<?php
/**
 * @package Module
 */

/**
 * @package Module
 */
class NethGui_Module_BreadCrumb extends NethGui_Core_Module_Abstract
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

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);

        if ($mode === self::VIEW_REFRESH) {
            $view['rootLine'] = $this->prepareRootline();
        }
    }

    private function prepareRootline()
    {
        $module = $this->currentModule;
        
        // XXX: skip breadcrumbs if in Notification
        if($module instanceof NethGui_Module_Notification) {
            return array();
        }
        
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

        return $rootLine;
    }

    public function renderBreadcrumbMenu(NethGui_Renderer_Abstract $view)
    {
        $content = implode('</li><li>', $view['rootLine']);

        if ( ! empty($content)) {
            $content = '<ul id="BreadCrumb"><li>' . $content . '</li></ul>';
        }

        return $content;
    }

}