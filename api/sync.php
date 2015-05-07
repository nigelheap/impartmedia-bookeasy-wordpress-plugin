<?php
    
    //you can cron this bro.

    define('WP_USE_THEMES', false);
    require(__DIR__ . '/../../../../wp-load.php');

    if (!current_user_can('manage_options') && PHP_SAPI != 'cli') {
        die('No Access');
    }

    global $wpdb;

    if (isset($argv)) {
        $type = isset($argv[1]) ? $argv[1] : 'sync';
    }
    else {
        $type = isset($_GET['type']) ? $_GET['type'] : 'sync';
    }

    if(PHP_SAPI != 'cli'): 
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
    endif; 
    $sync = new BookeasyOperators_Import();
    $result = $sync->$type();

    echo $result;
    echo '...Done';
    if(PHP_SAPI != 'cli'): 
?>
</body>
</html><?php 
    endif; 
?>