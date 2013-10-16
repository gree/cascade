<?php
/**
 *  Config.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Driver
 */

/**
 *  設定ファイルを扱うドライバー抽象定義
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Driver
 */
interface Cascade_Driver_Config extends Cascade_Driver_Driver
{
    // ----[ Interface Magic Methods ]--------------------------------
    // {{{ __construct
    /**
     *  コンストラクタ
     *
     *  @param  string   設定ファイルPATH
     */
    public /* void */
        function __construct(/* string */ $file_path);
    // }}}
    // ----[ Interface Methods ]--------------------------------------
    // {{{ get
    /**
     *  指定セクションの値を取得する
     *
     *  @param   string  セクション
     *  @param   mixed   (optional) 変数名
     *  @return  mixed   指定セクション格納された値
     */
    public /* mixed */
        function get(/* string */ $section,
                     /* mixed  */ $name = NULL);
    // }}}
    // {{{ get_all
    /**
     *  設定情報を全て取得する
     *
     *  @return  array   設定情報
     */
    public /* array */
        function get_all(/* void */);
    // }}}
    // {{{ count
    /**
     *  セクション数を取得する
     *
     *  @param   string  セクション
     *  @return  mixed   セクション数
     */
    public /* mixed */
        function count(/* void */);
    // }}}
};
