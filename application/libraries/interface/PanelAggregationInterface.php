<?php

interface PanelAggregationInterface {
    public function findPanel($panelIdentifier);
    public function attachPanel(PanelInterface $panel);
}
