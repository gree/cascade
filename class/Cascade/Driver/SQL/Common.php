<?php
/**
 *  Common.php
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_Driver
 *  @subpackage  SQL
 */

/**
 *  Cascade_Driver_SQL_Common
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_Driver
 *  @subpackage  SQL
 */
abstract class Cascade_Driver_SQL_Common
    extends    Cascade_Object
    implements Cascade_Driver_SQL
{
    // ----[ Properties ]---------------------------------------------
    // {{{ LOG
    /**
     *  ログの設定情報 (セクション名)
     *  @var  string
     */
    protected static $log_section    = 'log#sql';

    /**
     *  ログの設定情報 (セクション名)
     *  @var  string
     */
    protected static $log_config     = array(
        'level'        => 'level',
        'default_dir'  => 'dir.default',
        'dirs'         => array(
            Cascade_Driver_Log::LEVEL_IS_EMERG   => 'dir.emerg',
            Cascade_Driver_Log::LEVEL_IS_ALERT   => 'dir.alert',
            Cascade_Driver_Log::LEVEL_IS_CRIT    => 'dir.crit',
            Cascade_Driver_Log::LEVEL_IS_ERR     => 'dir.err',
            Cascade_Driver_Log::LEVEL_IS_WARNING => 'dir.warning',
            Cascade_Driver_Log::LEVEL_IS_NOTICE  => 'dir.notice',
            Cascade_Driver_Log::LEVEL_IS_INFO    => 'dir.info',
            Cascade_Driver_Log::LEVEL_IS_DEBUG   => 'dir.debug',
        ),
    );
    // }}}
    // ----[ Magic Methods ]------------------------------------------
    // {{{ __construct
    /**
     *  コンストラクタ
     *
     *  @param  string  Database-Source-Name
     */
    public /* void */
        function __construct(/* string */ $dsn)
    {
        parent::__construct();
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ get_logger
    /**
     *  ロガーを取得する
     *
     *  @return  Cascade_Driver_Log  ロガー
     */
    public static /* Cascade_Driver_Log */
        function get_logger(/* void */)
    {
        static $instance = NULL;

        // ロガーを取得する
        if ($instance == NULL) {
            $file_name = sprintf('%s.dat', date('Ymd'));
            $config    = Cascade_System_Config::get(self::$log_section, self::$log_config);
            $instance  = Cascade_Driver_Factory::getInstance(
                Cascade::DRIVER_LOG_FILE,
                $args = array($file_name, $config, $config['level'])
            );
        }
        return $instance;
    }
    // }}}
};