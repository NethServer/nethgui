<?php

final class DummyForm1Module extends StandardModule {
    public function render()
    {
        return $this->renderView('PanelView1', array('module'=>$this));
    }
}
