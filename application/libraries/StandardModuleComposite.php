<?php

/**
 * Implementation of ModuleCompositeInterface
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

    public function bind(RequestInterface $parameters)
    {
        parent::bind($parameters);
        foreach($this->getChildren() as $module)
        {
            if($parameters->hasParameter($module->getIdentifier()))
            {
                $module->bind($parameters->getParameterAsInnerRequest($module->getIdentifier()));
            }
        }
    }

    public function validate(ValidationReportInterface $report)
    {
        parent::validate($report);

        foreach($this->getChildren() as $module)
        {
            $module->validate($report);
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

