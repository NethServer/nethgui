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

final class Response implements ResponseInterface {

    private $responseData = NULL;

    private function __construct($identifier, &$responseData)
    {
        if(!isset($responseData))
        {
            $responseData = array();
        }

        $this->responseData = &$responseData;
    }

    public function createModuleResponse($moduleIdentifier)
    {

    }

    public function put($data)
    {
        
    }

    public function setValidationReport(ValidationReportInterface $report)
    {

    }

}