<?php
/**
 *  Driver.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Driver
 */

/**
 *  全てのドライバの基底インターフェース
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Driver
 */
interface Cascade_Driver_Driver
{
    // ----[ Interface Methods ]---------------------------------------
    /**
     *  利用可能なドライバか確認する
     *
     *  PHPの拡張モジュールの読み込み状態、<br/>
     *  バージョン情報を考慮しドライバの有効の有無を考慮して判断する
     *
     *  @return  boolean  TRUE:利用可能ドライバ
     */
    public static /* boolean */
        function is_enable(/* void */);

    /**
     *  ドライバーのバージョン情報を取得する
     *
     *  @return  int  バージョン情報
     */
    public static /* string */
        function get_version(/* void */);

    // ----[ Interface Methods ]---------------------------------------
    /**
     *  エラー・コードを取得する
     *
     *  @return  int  エラー・コード
     */
    public /* int */
        function get_error_code(/* void */);

    /**
     *  エラー・メッセージを取得する
     *
     *  @return  string  エラー・メッセージ
     */
    public /* string */
        function get_error_message(/* void */);

    /**
     *  ロガーを取得する
     *
     *  @retrun  Cascade_Driver_Log  ロガー
     */
    public static /* Cascade_Driver_Log */
        function get_logger(/* void */);
};