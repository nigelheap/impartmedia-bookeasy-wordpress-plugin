<?php

    ini_set('max_execution_time', 0);
    ini_set('max_input_time', '-1');
    ini_set('output_buffering', 'Off');

    set_time_limit(0);
    //you can cron this bro.

    define('WP_USE_THEMES', false);

    if(file_exists(__DIR__ . '/../../../../wp-load.php')){
        require(__DIR__ . '/../../../../wp-load.php');
    } else {
        require(__DIR__ . '/../../../wp-load.php');
    }

    if (!current_user_can('manage_options') && PHP_SAPI != 'cli') {
        die('No Access');
    }

    global $wpdb;




    if(!empty($argv)){
        $options = getopt('t::o::e::');

        $type = isset($options['t']) ? $options['t'] : 'sync';
        $operators = isset($options['o']) ? explode(',', $options['o']) : null;
        $email = isset($options['e']) ? $options['e'] : null;
    } else {
        $type = isset($_GET['type']) ? $_GET['type'] : 'sync';
        $operators = isset($_GET['op']) ? explode(',', $_GET['op']) : null;
        $email = null;
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
    $sync = new \Bookeasy\api\Import();

    if($type == 'cats'){
        $result = $sync->$type();
    } else {
        $result = $sync->$type($operators, $email);
    }
    
    echo $result;
    echo '...Done';
    if(PHP_SAPI != 'cli'): 
?>
</body>
</html><?php 
    endif; 
?>
