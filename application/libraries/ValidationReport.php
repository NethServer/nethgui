<?php

final class ValidationReport implements ValidationReportInterface {

    private $errors = array();

    public function addError($fieldId, $message)
    {
        $this->errors[] = array($fieldId, $message);
    }

    public function getErrors()
    {
        return $this->errors;
    }

}