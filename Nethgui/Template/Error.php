<!DOCTYPE HTML>
<html>
    <head>
        <title><?php echo $view->textLabel('title'); ?></title>        
    </head>
    <body>
        <?php echo $view->textLabel('title')->setAttribute('tag', 'h1'); ?>
        <?php echo $view->textLabel('text')->setAttribute('tag', 'div'); ?>
    </body>
</html>
