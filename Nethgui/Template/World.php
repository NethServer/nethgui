<?php
echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
$pageTitle = $view['CurrentModule']->translate($view['CurrentModule']->getModule()->getTitle());

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo $view['lang'] ?>" xml:lang="<?php echo $view['lang'] ?>">
    <head>
        <title><?php echo $pageTitle ?></title>
        <link rel="icon"  type="image/png"  href="<?php echo htmlspecialchars($view['favicon']) ?>" />
        <link rel="stylesheet" type="text/css" href="<?php echo htmlspecialchars($view['cssMain']); ?>" />
        <script type="text/javascript">document.write('<style type="text/css">#allWrapper {display:none}</style>')</script>
    </head>
    <body>
        <div id="allWrapper">
            <div id="pageHeader"><?php echo $view->inset('NotificationArea') ?><a id="product" href="Dashboard">NethServer</a><h1 id="ModuleTitle"><?php echo $pageTitle ?></h1></div>

            <div id="pageContent">
                <div class="primaryContent" role="mainTask">
                    <div class="<?php echo $view['CurrentModule']->getModule()->getIdentifier(); ?> CurrentModule"><?php echo $view->inset('CurrentModule') ?></div>
                </div>

                <div class="secondaryContent" role="otherTask">
                    <div class="Navigation Flat"><?php echo $view->inset('Menu') ?></div>
                </div>
            </div>
            
            <div id="HelpArea" class="HelpArea disabled">
                <div class="HelpAreaContent">
                    <?php echo $view->elementList($view::BUTTONSET)->insert($view->button('Hide', $view::BUTTON_CANCEL)); ?>
                    <div id="HelpAreaInnerDocument"></div>
                </div>
            </div>
        </div>        
<?php foreach ($view['js'] as $scriptPath): ?><script type="text/javascript" src="<?php echo htmlspecialchars($scriptPath) ?>" ></script><?php endforeach; ?>        
    </body>
</html>
