<fieldset><?php echo T('Confirm deletion of "%KEY%"?', array('%KEY%' => isset($parameters[$view['key']]) ? $parameters[$view['key']] : '%KEY%')); ?></fieldset>
<div><?php echo form_hidden($name[$view['key']], $parameters[$view['key']]) . form_submit('delete', T('Delete')) . anchor($view->buildUrl('..'), T('Cancel')); ?></div>
