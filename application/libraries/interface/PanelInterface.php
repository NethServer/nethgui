<?php
interface PanelInterface {

        public function getIdentifier();
        public function getTitle();
        public function getDescription();


        /**
         * @param ModuleInterface $module
         */
        public function setModule(ModuleInterface $module);
        /**
         * @return ModuleInterface
         */
        public function getModule();

        /**
         * @param array $parameters
         */
        public function bind($inputParameters);

        /**
         * @return boolean
         */
        public function validate();

        public function render();
}

