<?php
final class RemoteAccessModule extends FormModule implements TopModuleInterface {
    public function getTitle() {
        return "Remote access";
    }

    public function getParentMenuIdentifier()
    {
        return "SecurityModule";
    }
}