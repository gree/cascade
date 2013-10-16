<?php
/**
 * mysql.ini.php
 */
return $db_config_list = array(
    'default' => array(
        'user'      => 'root',
        'pass'      => 'gree',
        'ro_user'   => 'root',
        'ro_pass'   => 'gree',
    ),
    'test' => array(
        'master'    => 'localhost:13507',
        'slave'     => array(
            'localhost:13507',
        ),
        'standby'   => 'localhost:13507',
        'db'        => 'test',
    ),
);
// }}}
?>
