<?php

interface PanelCompositeInterface {

    public function addChild(PanelInterface $panel);

    public function getChildren();

}
