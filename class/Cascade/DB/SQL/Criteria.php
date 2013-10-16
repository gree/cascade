<?php
/**
 *  SQL.php
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_DB
 *  @subpackage  SQL
 */

/**
 *  SQLデータ抽出/実行条件の定義クラス
 *
 *  SQLサーバーへのデータ抽出/実行条件を定義するクラス
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_DB
 *  @subpackage  SQL
 */
final class Cascade_DB_SQL_Criteria
    extends Cascade_DB_Criteria
{
    // ----[ Class Constants ]----------------------------------------
    /** コマンド定義 : Get               */
    const TYPE_IS_GET            = 0x01;
    /** コマンド定義 : Multi Get         */
    const TYPE_IS_MGET           = 0x02;
    /** コマンド定義 : Fetch-All         */
    const TYPE_IS_FIND           = 0x03;
    /** コマンド定義 : Fetch-Value       */
    const TYPE_IS_VALUE          = 0x04;
    /** コマンド定義 : Execute           */
    const TYPE_IS_EXEC           = 0x05;
    /** コマンド定義 : Last Insert ID    */
    const TYPE_IS_LAST_INSERT_ID = 0x06;
    /** コマンド定義 : Begin Transaction */
    const TYPE_IS_BEGIN          = 0x07;
    /** コマンド定義 : Commit            */
    const TYPE_IS_COMMIT         = 0x08;
    /** コマンド定義 : RollBack          */
    const TYPE_IS_ROLLBACK       = 0x09;
    /** コマンド定義 : Create Database   */
    const TYPE_IS_CREATE_DB      = 0x0A;

    // ----[ Properties ]---------------------------------------------
    // {{{ @properties
    /**
     *  実行コマンド
     *
     *  クラス定数で定義されている抽出コマンドを指定する。<br/>
     *  TYPE_IS_FIND, TYPE_IS_VALUE, TYPE_IS_EXECを利用する場合は、<br/>
     *  Daynamc-queryを指定するために、stmt_nameプロパティー値を必要とする。
     *
     *  @var  int
     */
    protected /* int */     $type       = NULL;

    /**
     *  データ・フォーマット・クラス名
     *
     *  データ・フォーマット・クラスを指定する。<br/>
     *  クラス定義ファイルの読み込みはクラスローダーが行う。
     *
     *  @see  Cascade_DB_System_ClassLoader
     *  @var  string
     */
    protected /* string */  $df_name    = NULL;

    /**
     *  ステートメント名
     *
     *  指定されたデーター・フォーマットから
     *  Dynamic-Queryを取得する場合に指定する。<br/>
     *  抽出コマンド(TYPE_IS_FIND, TYPE_IS_VALUE, TYPE_IS_EXEC)によって<br/>
     *  プロパティ値の指定が必修になる。
     *
     *  @see  Cascade_DB_DataFormat::getDynamicQuery
     *  @var  string
     */
    protected /* string */  $stmt_name  = NULL;

    /**
     *  クエリー実行パラメーター
     *
     *  ステートメント・プレースホルダーにバインドする値を指定する。
     *
     *  @var  mixed
     */
    protected /* mixed */   $params     = NULL;

    /**
     *  オフセット値
     *
     *  データ抽出条件 : OFFSETにバインドされる値。<br/>
     *  最大取得件数の指定とペアで利用する必要がある。<br/>
     *  オフセット値のみ指定された場合は例外が発生する。
     *  実行パラメーターには指定しない。
     *   - バインド変数の型(Z_TYPE)指定をIS_LONG固定にするため。
     *
     *  @var  int
     */
    protected /* int */     $offset     = NULL;

    /**
     *  最大取得件数
     *
     *  データ抽出条件 : LIMITにバインドされる値。
     *  実行パラメーターには指定しない。
     *   - バインド変数の型(Z_TYPE)指定をIS_LONG固定にするため。
     *
     *  @var  int
     */
    protected /* int */     $limit      = NULL;

    /**
     *  接続DSN参照/テーブル参照/etc...の基準値
     *
     *  指定された値により、接続DSN参照/テーブル参照が確定する。
     *
     *  @var  int
     */
    protected /* int */     $hint       = NULL;

    /**
     *  マスター接続フラグ
     *
     *  読み込み処理の接続を強制的にマスターサーバーに接続にする。<br/>
     *  実行コマンド(TYPE_IS_EXEC)の場合は常にマスターが参照される。
     *
     *  @var  boolean
     */
    protected /* boolean */ $use_master = FALSE;

    /**
     *  拡張DSN指定
     *
     *  読み込み処理の接続を強制的に指定DSN接続にする。<br/>
     *  実行コマンド(TYPE_IS_EXEC)の場合は常にマスターが参照される。
     *
     *  @var  array
     */
    protected /* array */   $extra_dsn  = NULL;
    // }}}
};
