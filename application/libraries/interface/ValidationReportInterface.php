<?php


interface ValidationReportInterface {
    public function addError($fieldId, $message);

    /**
     * @return array
     */
    public function getErrors();
}
