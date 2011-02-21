<?php

final class Dummy1Module extends StandardCompositeModule implements TopModuleInterface {

    public function getDescription()
    {
        return "This is not a real module. This is a DUMMY!";
    }

    public function getTitle()
    {
        return "Dummy module 1";
    }

    public function getParentMenuIdentifier()
    {
        return NULL;
    }


}
?>
