<?php

interface PanelAggregationInterface {
    /**
     * @return PanelInterface
     */
    public function findPanel($panelIdentifier);

    /**
     * @param PanelInterface $panel
     */
    public function attachPanel(PanelInterface $panel);
}
