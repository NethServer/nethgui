<?php echo '<?xml version="1.0" encoding="utf-8"?>' . "\n"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo $view['lang'] ?>" xml:lang="<?php echo $view['lang'] ?>">
    <head>
        <title><?php echo T($view['title']) ?></title>
    </head>
    <body>
        <?php echo $view->inset('content'); ?>
    </body>
</html>
