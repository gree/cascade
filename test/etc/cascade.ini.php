<?php
return $cascade_config = array(
    'system' => array(
        // {{{ log
        'log' => array(
            'level' => 7,
            'dir'   => array(
                'default' => '/tmp/cascade',
                'emerg'   => NULL,
                'alert'   => NULL,
                'crit'    => NULL,
                'err'     => NULL,
                'warning' => NULL,
                'notice'  => NULL,
                'info'    => NULL,
                'debug'   => NULL,
            ),
        ),
        // }}}
        // {{{ log#sql
        'log#sql : log' => array(
            'dir' => array(
                'default' => '/tmp/cascade/mysql',
            ),
        ),
        // }}}
        // {{{ log#kvs
        'log#kvs : log' => array(
            'dir' => array(
                'default' => '/tmp/cascade/kvs',
            ),
        ),
        // }}}
        // {{{ dsn-config-gree
        'dsn-config-gree' => array(
            // gree(mysql)
            'mysql.var'     => 'db_config_list',
            'mysql.path'    => dirname(__FILE__).'/mysql.ini.php',
            // gree(memcache)
            'memcache.var'  => 'memcache_config_list',
            'memcache.path' => dirname(__FILE__).'/memcache.ini.php',
            // gree(flare)
            'flare.var'     => 'flare_config_list',
            'flare.path'    => dirname(__FILE__).'/flare.ini.php',
        ),
        // }}}
    ),
    'schema' => array(
        // {{{ default
        'default' => array(
            'dataformat' => array(
                'prefix' => 'Cascade_DataFormat_',
                'suffix' => NULL,
            ),
            'gateway'    => array(
                'prefix' => 'Cascade_Gateway_',
                'suffix' => NULL,
            ),
        ),
        // }}}
        // {{{ test
        'test' => array(
            'dataformat' => array(
                'prefix' => 'Cascade_',
                'suffix' => '_DataFormat',
            ),
            'gateway'    => array(
                'prefix' => 'Cascade_',
                'suffix' => '_Gateway',
            ),
        ),
        // }}}
    ),
);
