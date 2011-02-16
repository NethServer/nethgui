<?php

final class Dummy2Module extends StandardModule {

    /**
     * @var Dummy1Panel
     */
    private $myPanel;

    public function getDescription()
    {
        return "This is a descendant of module 1.";
    }

    public function getTitle()
    {
        return "Dummy module 2";
    }

    public function getParentIdentifier()
    {
        return "Dummy1Module";
    }

    public function getPanel() {
       if(!isset($this->myPanel)) {
           $this->myPanel = new FormPanel($this->getIdentifier(), NULL);
           $this->myPanel->setPolicyDecisionPoint($this->getPolicyDecisionPoint());
           $this->myPanel->addChild(new Dummy1Panel('UserData1-Dummy1Panel'));
       }
       return $this->myPanel;
    }

}
?>
