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

    public function bind(ParameterDictionaryInterface $parameters)
    {
        parent::bind($parameters);
        foreach($this->getChildren() as $module)
        {
            if($parameters->hasKey($module->getIdentifier()))
            {
                $module->bind($parameters->getValueAsParameterDictionary($module->getIdentifier()));
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
    protected function render()
    {
        $output = '';
        foreach ($this->getChildren() as $module)
        {
            $output .= $module->render();
        }
        return $this->decorate($output);
    }

    /**
     * Called after children have been rendered.
     *
     * @param string $output Children output
     * @return string Decorated children output
     */
    protected function decorate($output)
    {
        return $output;
    }

}

