<p><?php echo T('Confirm deletion of "%KEY%"?', array('%KEY%'=>$parameters['key'])); ?></p>
<div><?php echo form_submit('delete', T('Delete')) . anchor($view->buildUrl('..'), T('Cancel')); ?></div>
