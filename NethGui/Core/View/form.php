<?php
echo form_open_multipart(uri_string());
echo $view->renderView('NethGui_Core_View_container', $viewState);
echo '<div style="text-align: right"><input id="' . $id['save'] . '" name="' . $name['save'] . '" type="submit" value="Save" /></div>';
echo form_close();
?>
