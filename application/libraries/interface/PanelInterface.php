<?php
interface PanelInterface {

        public function getIdentifier();
        public function getTitle();
        public function getDescription();

        
        /**
         * @param array $parameters
         */
        public function bind($parameters);

        /**
         * @return boolean
         */
        public function validate();

        public function render();
}

