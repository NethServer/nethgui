<?php

final class Dummy2Module extends StandardModule {

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

}
?>
