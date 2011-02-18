<?php

final class Dummy2Module extends FormPanel implements ModuleMenuInterface {

    public function  __construct()
    {
        parent::__construct(get_class($this));
    }

    public function createChildren() 
    {
        $this->addChild(new DummyForm1Module('UserData1'));
        $this->addChild(new DummyForm1Module('UserData2'));
        $this->addChild(new DummyForm1Module('UserData3'));        
    }

    public function getDescription()
    {
        return 'This is a descendant of "module 1."';
    }

    public function getTitle()
    {
        return "Dummy module 2";
    }

    public function getParentMenuIdentifier()
    {
        return "Dummy1Module";
    }



}

?>
