<?php

final class NethGui_Core_Module_ValidationReport extends NethGui_Core_Module_Standard {

    /**
     *
     * @var NethGui_Core_ValidationReportInterface
     */
    private $report;


    public function __construct(NethGui_Core_ValidationReportInterface $report)
    {
        parent::__construct();
        $this->report =$report;
    }

    public function prepareResponse(NethGui_Core_ResponseInterface $response)
    {
        $this->parameters['errors'] = $this->report->getErrors();
        parent::prepareResponse($response);
    }
}
