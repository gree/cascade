<?php
return $db_config_list = array(
    'default' => array(
        'user'      => 'travis',
        'pass'      => '',
        'ro_user'   => 'travis',
        'ro_pass'   => '',
    ),
    'test' => array(
        'master'    => 'localhost:3306',
        'slave'     => array(
            'localhost:3306',
        ),
        'standby'   => 'localhost:3306',
        'db'        => 'cascade_test_on_travis',
    ),
);
