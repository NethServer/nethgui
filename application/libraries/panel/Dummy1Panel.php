<?php

final class Dummy1Panel extends StandardPanel {

    public function render()
    {
        return $this->renderView('PanelView1');
    }

}