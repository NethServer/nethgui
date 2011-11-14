<?php $moduleTitle = $view['CurrentModule']->translate($view['CurrentModule']->getModule()->getTitle()); $pageTitle = $view['company']." - ".$moduleTitle; ?><!DOCTYPE html>
<html lang="<?php echo $view['lang'] ?>">
    <head>
        <title><?php echo $pageTitle ?></title>
        <link rel="icon"  type="image/png"  href="<?php echo htmlspecialchars($view['favicon']) ?>" />
        <?php foreach ($view['css'] as $cssPath): ?> <link rel="stylesheet" type="text/css" href="<?php echo htmlspecialchars($cssPath) ?>" /><?php endforeach; ?>        
        <script type="text/javascript">document.write('<style type="text/css">#allWrapper {display:none}</style>')</script>
        <meta name="viewport" content="width=device-width" />
        <?php foreach ($view['js'] as $scriptPath): ?><script type="text/javascript" src="<?php echo htmlspecialchars($scriptPath) ?>" ></script><?php endforeach; ?>        
    </head>
    <body>
        <div id="allWrapper">
            <div id="pageHeader"><?php echo $view->inset('NotificationArea') ?><a id="product" href="Dashboard" title='NethServer'></a><h1 id="ModuleTitle"><?php echo $moduleTitle ?></h1><h2>NethServer</h2></div>

            <div id="pageContent">
                <div class="primaryContent" role="mainTask">
                    <div class="<?php echo $view['CurrentModule']->getModule()->getIdentifier(); ?> CurrentModule"><?php echo $view->inset('CurrentModule') ?></div>
                    <div id='footer'><?php echo htmlspecialchars($view['company']." - ".$view['address']); ?></div>
                </div>

                <div class="secondaryContent" role="otherTask">
                    <h2><?php echo htmlspecialchars($view->translate('Other modules')) ?></h2>
                    <?php echo $view->inset('Menu') ?>
                </div>
            </div>
            
            <div id="HelpArea" class="HelpArea disabled">
                <div class="HelpAreaContent">
                    <?php echo $view->elementList($view::BUTTONSET)->insert($view->button('Hide', $view::BUTTON_CANCEL)); ?>
                    <div id="HelpAreaInnerDocument"></div>
                </div>
            </div>
        </div>       
    </body>
</html>
