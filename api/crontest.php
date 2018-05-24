<?php
    
    //you can cron this bro.

    define('WP_USE_THEMES', false);
    require(__DIR__ . '/../../../../wp-load.php');

    if (!current_user_can('manage_options') && PHP_SAPI != 'cli') {
        die('No Access');
    }

    add_action('wp_mail_failed', 'log_mailer_errors', 10, 1);
    function log_mailer_errors($wp_error){
        print_r($wp_error);
    }

    $message = array();
    $message[] = 'Cron test';
    $message[] = 'Run at '.date('r');

    $emails = 'nigel@nigelheap.com';

    $result = wp_mail(
        array($emails), 
        get_bloginfo('name') . ' cron test', 
        implode(PHP_EOL, $message)
    );

    var_dump($result);

    $result = mail($emails, 'cron test', implode(PHP_EOL, $message));

    var_dump($result);