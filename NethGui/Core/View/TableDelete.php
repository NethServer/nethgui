<fieldset><?php echo T('Confirm deletion of "%s"?', array('%s' => isset($parameters[$view['__key']]) ? $parameters[$view['__key']] : '%s')); ?></fieldset>
<div><?php 

    echo form_hidden($name[$view['__key']], $parameters[$view['__key']]);
    
    $submitConf = array('name' => $module->getIdentifier());

    if ($view['__action'] == 'index') {
        $submitConf['disabled'] = 'disabled';
    }

    echo form_submit($submitConf, T($module->getIdentifier()));
    
    echo anchor($view->buildUrl('..'), T('Cancel'));


?></div>
