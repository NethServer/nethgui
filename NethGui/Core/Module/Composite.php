<?php
/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * TODO: describe class
 *
 * @package NethGuiFramework
 * @subpackage StandardImplementation
 */
abstract class NethGui_Core_Module_Composite extends NethGui_Core_Module_Standard implements NethGui_Core_ModuleCompositeInterface
{

    private $children = array();

    /**
     * Propagates initialize() message to children.
     */
    public function initialize()
    {
        parent::initialize();
        foreach ($this->children as $child) {
            if ( ! $child->isInitialized()) {
                $child->initialize();
            }
        }
    }

    public function addChild(NethGui_Core_ModuleInterface $childModule)
    {
        if ( ! isset($this->children[$childModule->getIdentifier()])) {
            $this->children[$childModule->getIdentifier()] = $childModule;
            $childModule->setParent($this);
            if ($this->isInitialized() && ! $childModule->isInitialized()) {
                $childModule->initialize();
            }
            if ($childModule instanceof NethGui_Core_RequestHandlerInterface) {
                $this->setRequestHandler($childModule->getIdentifier(), $childModule);
            }
        }
    }

    public function getChildren()
    {
        // TODO: authorize access request on policy decision point.
        return array_values($this->children);
    }

    public function bind(NethGui_Core_RequestInterface $request)
    {
        parent::bind($request);
        foreach ($this->getChildren() as $module) {
            $module->bind($request->getParameterAsInnerRequest($module->getIdentifier()));
        }
    }

    public function validate(NethGui_Core_ValidationReportInterface $report)
    {
        parent::validate($report);
        foreach ($this->getChildren() as $module) {
            $module->validate($report);
        }
    }

    public function process(NethGui_Core_ResponseInterface $response)
    {
        parent::process($response);
        foreach ($this->getChildren() as $childModule) {
            $childModule->process($response->getInnerResponse($childModule));
        }
    } 
}

