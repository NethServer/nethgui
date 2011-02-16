<?php

interface ModuleInterface {

    /**
     * @return string Unique module identifier
     */
    public function getIdentifier();
    
    /**
     * @return string Unique parent module identifier
     */
    public function getParentIdentifier();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return PanelInterface implementing object.
     */
    public function getPanel();

    /**
     * Attach the Module instance to an aggregation.
     */
    public function aggregate(ModuleAggregationInterface $aggregation);

}



