<?php
/**
 *  Cascade Configuration
 */
return $cascade_config = array(
    'system' => array(
        // {{{ log
        'log' => array(
            'level' => 7,
            'dir'   => array(
                // デフォルト出力先
                'default' => '/var/log/cascade',
                // エラー毎に出力先を変更する場合
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
                'default' => '/var/log/cascade/mysql',
            ),
        ),
        // }}}
        // {{{ log#kvs
        'log#kvs : log' => array(
            'dir' => array(
                'default' => '/var/log/cascade/kvs',
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
