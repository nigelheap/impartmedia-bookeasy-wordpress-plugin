<?php
    
    ini_set('max_execution_time', 0);
    ini_set('max_input_time', '-1');
    set_time_limit(0);
    //you can cron this bro.

    define('WP_USE_THEMES', false);
    require(__DIR__ . '/../../../../wp-load.php');

    if (!current_user_can('manage_options') && PHP_SAPI != 'cli') {
        die('No Access');
    }

    global $wpdb;

    if (isset($argv)) {
        $type = isset($argv[1]) ? $argv[1] : 'sync';
    } else {
        $type = isset($_GET['type']) ? $_GET['type'] : 'sync';
    }


    if (isset($argv)) {
        $operators = isset($argv[2]) ? explode(',', $argv[2]) : null;
    } else {
        $operators = isset($_GET['op']) ? explode(',', $_GET['op']) : null;
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
    if($type == 'cats'){
        $result = $sync->$type();
    } else {
        $result = $sync->$type($operators);
    }
    
    echo $result;
    echo '...Done';
    if(PHP_SAPI != 'cli'): 
?>
</body>
</html><?php 
    endif; 
?>
