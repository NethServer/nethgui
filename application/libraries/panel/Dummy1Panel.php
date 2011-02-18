<?php

final class Dummy1Panel extends StandardPanel {
    public function render()
    {
        $pdpName = get_class($this->getPolicyDecisionPoint());
        return $this->renderView('PanelView1', array('pdpName'=>$pdpName, 'panel'=>$this));
    }
}