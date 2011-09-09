<?php echo '<?xml version="1.0" encoding="utf-8"?>' . "\n"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo $view['lang'] ?>" xml:lang="<?php echo $view['lang'] ?>">
    <head>
        <title>NethServer</title>
        <link rel="stylesheet" type="text/css" href="<?php echo htmlspecialchars($view['cssMain']); ?>" />
    </head>
    <body>
        <div id="allWrapper"> 
            <div id="header"><div id="product">NethServer</div><?php echo $view['NotificationArea'] ?></div>
            <div class="colmask leftmenu">
                <div class="colleft">
                    <div class="col1"><div class="<?php echo $view['CurrentModule']->getModule()->getIdentifier(); ?> CurrentModule"><?php echo $view['CurrentModule'] ?></div></div>
                    <div class="col2"><div id="moduleMenu" class="ModuleMenu"><?php echo $view['Menu'] ?></div></div>
                </div>
            </div>
            
        </div>        
        <?php foreach ($view['js'] as $scriptPath): ?><script type="text/javascript" src="<?php echo htmlspecialchars($scriptPath) ?>" ></script><?php endforeach; ?>        
    </body>
</html>
