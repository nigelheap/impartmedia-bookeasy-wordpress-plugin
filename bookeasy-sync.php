<?php
    
    //you can cron this bro.

    define('WP_USE_THEMES', false);
    require('../../../wp-load.php');

    if (!current_user_can('manage_options') && PHP_SAPI != 'cli') {
        die('No Access');
    }

    global $wpdb;

?>    
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Syncing</title>
    <style type="text/css">
        body{
            font-family: sans-serif;
            background: #eee;
            color: #444;
            font-family: "Open Sans",sans-serif;
            font-size: 13px;
            line-height: 1.4em;
        }
    </style>
</head>
<body>
<?php 
    
    $sync = new BookeasyOperators_Import();
    $result = $sync->sync();

    echo $result;
    echo '...Done';
?>
</body>
</html>