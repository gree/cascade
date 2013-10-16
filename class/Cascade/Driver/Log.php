<?php
/**
 *  Log.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Log
 */

/**
 *  ログを扱うドライバー抽象定義
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Log
 */
interface Cascade_Driver_Log extends Cascade_Driver_Driver
{
    // ----[ Class Constants ]----------------------------------------
    // {{{ ERROR LEVEL
    /**
     *  System is unusable
     */
    const LEVEL_IS_EMERG   = 0;

    /**
     *  Immediate action required
     */
    const LEVEL_IS_ALERT   = 1;

    /**
     *  Critical conditions
     */
    const LEVEL_IS_CRIT    = 2;

    /**
     *  Error conditions
     */
    const LEVEL_IS_ERR     = 3;

    /**
     *  Warning conditions
     */
    const LEVEL_IS_WARNING = 4;

    /**
     *  Normal but significant
     */
    const LEVEL_IS_NOTICE  = 5;

    /**
     *  Informational
     */
    const LEVEL_IS_INFO    = 6;

    /**
     *  Debug-level messages
     */
    const LEVEL_IS_DEBUG   = 7;
    // }}}
    // ----[ Interface Magic Methods ]--------------------------------
    // {{{ __construct
    /**
     *  コンストラクタ
     */
    public /* void */
        function __construct(/* string */ $name,
                             /* array  */ $config = array(),
                             /* int    */ $level  = self::LEVEL_IS_INFO);
    // }}}
    // ----[ Interface Methods ]--------------------------------------
    // {{{ emerg
    /**
     *  ログ出力処理 :: EMERGENCY
     *
     *  @param  mixed  ログ情報
     */
    public /* void */
        function emerg(/* mixed */ $input);
    // }}}
    // {{{ alert
    /**
     *  ログ出力処理 :: ALERT
     *
     *  @param  mixed  ログ情報
     */
    public /* void */
        function alert(/* mixed */ $input);
    // }}}
    // {{{ crit
    /**
     *  ログ出力処理 :: CRITICAL
     *
     *  @param  mixed  ログ情報
     */
    public /* void */
        function crit(/* mixed */ $input);
    // }}}
    // {{{ err
    /**
     *  ログ出力処理 :: ERROR
     *
     *  @param  mixed  ログ情報
     */
    public /* void */
        function err(/* mixed */ $input);
    // }}}
    // {{{ warn
    /**
     *  ログ出力処理 :: WARNING
     *
     *  @param  mixed  ログ情報
     */
    public /* void */
        function warn(/* mixed */ $input);
    // }}}
    // {{{ notice
    /**
     *  ログ出力処理 :: NOTICE
     *
     *  @param  mixed  ログ情報
     */
    public /* void */
        function notice(/* mixed */ $input);
    // }}}
    // {{{ info
    /**
     *  ログ出力処理 :: INFOMATION
     *
     *  @param  mixed  ログ情報
     */
    public /* void */
        function info(/* mixed */ $input);
    // }}}
    // {{{ debug
    /**
     *  ログ出力処理 :: DEBUG
     *
     *  @param  mixed  ログ情報
     */
    public /* void */
        function debug(/* mixed */ $input);
    // }}}
    // ----[ Interface Methods ]--------------------------------------
    // {{{ get_stack
    /**
     *  スタックされたログ情報を取得する
     *
     *  @param  mixed  ログ情報
     */
    public /* void */
        function get_stack(/* void */);
    // }}}
};
