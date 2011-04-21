<?php
/**
 * @package Core
 * @subpackage Table
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * @package Core
 * @subpackage Table
 */
class NethGui_Core_Table_Controller extends NethGui_Core_Module_Controller {

    private $database;

    private $type;

    private $columns;

    public function __construct($identifier, $database, $type, $columns)
    {
        parent::__construct($identifier);
        $this->database = $database;
        $this->type = $type;
        $this->columns = array_values($columns);
        $this->viewTemplate = array($this, 'renderCurrentView');

        // XXX: can we instantiate only the required actions after bind()?
        $this->addChild(new NethGui_Core_Table_ActionRead('read'));
        $this->addChild(new NethGui_Core_Table_ActionCreate('create'));
        $this->addChild(new NethGui_Core_Table_ActionDelete('delete'));
        $this->addChild(new NethGui_Core_Table_ActionUpdate('update'));

    }

    public function getTableColumns() {
        return $this->columns;
    }

    public function getKeyType() {
        return $this->type;
    }

    public function getDatabase() {
        return $this->getHostConfiguration()->getDatabase($this->database);
    }

    public function renderCurrentView($state) {
        return $state['view'][$this->currentAction->getIdentifier()]->render();
    }
}

