<?php

final class Dummy2Module extends FormModule implements TopModuleInterface {

    public function  __construct()
    {
        parent::__construct(get_class($this));
    }
    
    public function initialize()
    {
        $container = new ContainerModule("c1");        
        $container->addChild(new DummyForm1Module('UserData1'));
        $container->addChild(new DummyForm1Module('UserData2'));

        $this->addChild($container);
        $this->addChild(new DummyForm1Module('UserData3'));

        parent::initialize();
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
