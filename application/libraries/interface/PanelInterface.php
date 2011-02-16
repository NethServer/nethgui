<?php
interface PanelInterface {

        public function getIdentifier();

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

