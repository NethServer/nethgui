<?php

class NethGui_Core_Module_TableDialog extends NethGui_Core_Module_Standard
{

    private $dbSchema;
    private $dialogTemplate;

    /**
     *
     * @param string|array $template See {@link NethGui_Core_View::setTemplate()} method.
     * @param array $dbSchema
     */
    public function __construct($template, $dbSchema)
    {
        parent::__construct();
        $this->dialogTemplate = $template;
        $this->dbSchema = $dbSchema;
        $this->autosave = FALSE;
    }

    public function initialize()
    {
        parent::initialize();
        foreach ($this->dbSchema as $d) {
            $this->declareParameter($d[0], $d[1], NULL, $d[2]);
        }
    }

    /**
     * Sends an onDialogSave() message to parent Module, if it exists.
     */
    public function process()
    {
        parent::process();

        $parentModule = $this->getParent();
        if ($parentModule instanceOf NethGui_Core_ModuleInterface
            && method_exists($parentModule, 'onDialogSave')) {
            $values =  $this->parameters->getArrayCopy();
            if ( ! empty($values)) {
                $key = array_shift($values);
                $parentModule->onDialogSave($key, $values);
            }
        }
    }

    public function loadValues($key, $values)
    {
        // set the "primary key": the unique identifier
        $this->parameters[$this->dbSchema[0][0]] = $key;
        foreach ($values as $name => $value) {
            $this->parameters[$name] = $value;
        }
    }

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        $view->setTemplate($this->dialogTemplate);
    }

}