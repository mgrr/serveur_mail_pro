<?php
$config['imap_conn_options'] = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true,
    ),
);
$config['smtp_conn_options'] = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true,
    ),
);
$config['smtp_user'] = '%u';
$config['smtp_pass'] = '%p';
$config['plugins'] = array_merge($config['plugins'], ['twofactor_gauthenticator']);
$config['log_logins'] = true;
$config['login_autocomplete'] = 0;
$config['max_recipients'] = 50;
