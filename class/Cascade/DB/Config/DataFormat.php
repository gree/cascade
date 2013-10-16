<?php
/**
 *  DataFormat.php
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_DB
 *  @subpackage  Config
 */

/**
 *  設定ファイル情報定義クラス
 *
 *  設定ファイル情報の各種値を設定する。<br />
 *  設定ファイルのデータ取得の形式、使用するドライバーもテーブル定義毎に行う。
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_DB
 *  @subpackage  Config
 */
abstract class Cascade_DB_Config_DataFormat
    extends    Cascade_DB_DataFormat
{
    // ----[ Class Constants ]----------------------------------------
    /** ドライバー形式 : PHP ARRAYファイル */
    const DRIVER_PHPARRAY   = Cascade::DRIVER_PHPARRAY;
    /** ドライバー形式 : Iniファイル */
    const DRIVER_INIFILE    = Cascade::DRIVER_INIFILE;
    /** ドライバー形式 : CSVファイル */
    const DRIVER_CSVFILE    = Cascade::DRIVER_CSVFILE;

    // ----[ Class Constants ]----------------------------------------
    /** データ取得形式 : 添字配列 */
    const FETCH_MODE_NUM    = Cascade_DB_Config_Statement::FETCH_MODE_NUM;
    /** データ取得形式 : 連想配列 */
    const FETCH_MODE_ASSOC  = Cascade_DB_Config_Statement::FETCH_MODE_ASSOC;

    // ----[ Properties ]---------------------------------------------
    // @var string  設定ファイル格納PATH
    protected $config_path  = NULL;
    // @var string  設定ファイル
    protected $config_file  = NULL;
    // @var int     DB接続ドライバー種別
    protected $driver_type  = self::DRIVER_INIFILE;
    // @var int    結果のフェッチモード
    protected $fetch_mode   = self::FETCH_MODE_ASSOC;
    // @var array   割り込み処理定義
    protected $interceptors = array();

    // ----[ Methods ]------------------------------------------------
    // {{{ getConfigFilePath
    /**
     *  設定ファイルの取得
     *
     *  @return  string  ファイルPATH
     */
    public /* string */
        function getConfigFilePath(/* void */)
    {
        return sprintf('%s/%s', $this->config_path, $this->config_file);
    }
    // }}}
    // {{{ getFetchMode
    /**
     *  結果データの取得形式を取得する
     *
     *  設定されている結果データの取得形式情報を取得する。
     *
     *  @return  フェッチモード
     */
    public final /* int */
        function getFetchMode(/* void */)
    {
        return $this->fetch_mode;
    }
    // }}}
};