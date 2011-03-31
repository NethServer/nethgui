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
class NethGui_Core_Module_TableUpdater extends NethGui_Core_Module_Standard
{

    private $database;
    private $type;
    private $key;
    private $viewTemplate;

    /**
     *
     * @param string $database
     * @param string $key
     * @param string $type
     */
    public function __construct($database, $key, $type)
    {
        if (strlen($key) == 0) {
            throw new Exception("Key value must be a non-zero length string");
        }

        if (strlen($type) == 0) {
            throw new Exception("Type value must be a non-zero length string");
        }

        parent::__construct($key);
        $this->autosave = FALSE;
        $this->database = $database;
        $this->type = $type;
        $this->key = $key;
    }

    /**
     * Setup form view.
     * @param string $template
     */
    public function setViewTemplate($template)
    {
        $this->viewTemplate = $template;
    }

    public function process()
    {
        parent::process();

        $props = array();

        $setKeySuccess = $this->getHostConfiguration()->getDatabase($this->database)->setKey($this->key, $this->type, $props);

        if ($setKeySuccess) {
            $this->parameters = new NethGui_Core_ParameterSet();
        } else {
            throw new Exception("Unexpected failure of `setKey` operation");
        }
    }
    
    /**
     * Override this method to map module parameters to props.
     * @param array $parameters Current parameters values
     * @return array Prop associative-array
     */
    protected function prepareProps($parameters)
    {
        return $parameters;
    }


    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        if ( ! empty($this->viewTemplate)) {
            $view->setTemplate($this->viewTemplate);
        }
    }


}
