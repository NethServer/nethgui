<?php

final class Response  {

    const HTML = 0;
    const JS = 1;

    public function __construct($viewType)
    {
        $this->viewType = $viewType;
    }

    public function getViewType()
    {
        return $this->viewType;
    }

}
