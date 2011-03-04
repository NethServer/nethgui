<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 3.2//EN">
<html>
    <head>
        <title>NethGui</title>
        <link rel="stylesheet" type="text/css" href="<?php echo $css_main; ?>" />
        <?php foreach ($js as $scriptPath): ?>
            <script type="text/javascript" src="<?php echo $scriptPath ?>" ></script>
        <?php endforeach ?>
    </head>
    <body>
        <div id="allWrapper">
            <div id="header">NethGui</div>
            <div class="colmask leftmenu">
                <div class="colleft">
                    <div class="col1">
                        <div id="breadcrumbMenu"><?php echo $breadcrumb_menu ?></div>
                        <div id="moduleContent"><?php echo $module_content ?></div>
                    </div>
                    <div class="col2"><div id="moduleMenu"><?php echo $module_menu ?></div></div>
                </div>
            </div>
            <div id="footer">
                Powered by <a href="<?php echo site_url('../doc') ?>">NethGuiFramework</a> &ndash; Copyright 2011 &copy; Nethesis S.r.l
            </div>
        </div>
    </body>
</html>
