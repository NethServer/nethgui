<?php

final class DummyForm1Module extends StandardModule {

    public function renderView(Response $response)
    {
        if ($response->getViewType() === Response::HTML)
        {
            return $this->renderCodeIgniterView('PanelView1');
        }
    }

}
