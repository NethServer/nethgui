<!DOCTYPE html>
<html lang="<?php echo $view['lang'] ?>">
    <head>
        <title><?php echo $view['moduleTitle'] ?></title>
    </head>
    <body>
        <div id="allWrapper">
            <?php if ( ! $view['disableHeader']): ?>
                <div id="pageHeader">
                    <h1 id="ModuleTitle"><?php echo htmlspecialchars($view['moduleTitle']) ?></h1>
                </div>
            <?php endif; ?>
            <div id="pageContent">
                <div class="primaryContent" role="main">
                    <div id="CurrentModule"><?php echo $view['notificationOutput'] . $view['currentModuleOutput'] . $view['trackerOutput'] ?></div>
                    <?php if ( ! $view['disableFooter']): ?><div id="footer"><p><?php echo htmlspecialchars($view['company'] . ' - ' . $view['address']) ?></p></div><?php endif; ?>
                </div>
                <?php if ( ! $view['disableMenu']): ?><div class="secondaryContent" role="menu"><h2><?php echo htmlspecialchars($view->translate('Other modules')) ?></h2><?php echo $view['menuOutput'] . $view['logoutOutput'] ?></div><?php endif; ?>
            </div><?php echo $view['helpAreaOutput'] ?>
        </div><?php echo $view->literal($view['Resource']['js']) ?>
    </body>
</html>
