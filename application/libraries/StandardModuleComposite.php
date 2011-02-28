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
abstract class StandardModuleComposite extends StandardModule implements ModuleCompositeInterface {

    private $children = array();

    /**
     * Propagates initialize() message to children.
     */
    public function initialize()
    {
        parent::initialize();
        foreach ($this->children as $child)
        {
            if ( ! $child->isInitialized())
            {
                $child->initialize();
            }
        }
    }

    public function addChild(ModuleInterface $childModule)
    {
        if ( ! isset($this->children[$childModule->getIdentifier()]))
        {
            $this->children[$childModule->getIdentifier()] = $childModule;
            $childModule->setParent($this);
            if ($this->isInitialized() && ! $childModule->isInitialized())
            {
                $childModule->initialize();
            }
        }
    }

    public function getChildren()
    {
        // TODO: authorize access request on policy decision point.
        return array_values($this->children);
    }

    public function bind(RequestInterface $request)
    {
        parent::bind($request);
        foreach ($this->getChildren() as $module)
        {
            $module->bind($request->getParameterAsInnerRequest($module->getIdentifier()));
        }
    }

    public function validate(ValidationReportInterface $report)
    {
        parent::validate($report);
        foreach ($this->getChildren() as $module)
        {
            $module->validate($report);
        }
    }

    public function process()
    {
        foreach ($this->getChildren() as $childModule)
        {
            $childModule->process();
        }
    }

    /**
     * Default implementation of a ModuleComposite forwards the rendering
     * process to children modules.
     *
     * @return string
     */
    public function renderView(Response $response)
    {
        $output = '';
        foreach ($this->getChildren() as $module)
        {
            $output .= $module->renderView($response);
        }
        return $this->decorate($output, $response);
    }

    /**
     * Called after children have been rendered.
     *
     * @param string $output Children output
     * @return string Decorated children output
     */
    protected function decorate($output, Response $response)
    {
        return $output;
    }

}

