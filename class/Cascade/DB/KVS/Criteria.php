<?php
/**
 *  Criteria.php
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_DB
 *  @subpackage  KVS
 */

/**
 *  KVSデータ抽出/実行条件の定義クラス
 *
 *  Key-Value-Storeへのデータ抽出/実行条件を定義するクラス
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_DB
 *  @subpackage  KVS
 */
final class Cascade_DB_KVS_Criteria
    extends Cascade_DB_Criteria
{
    // ----[ Class Constants ]----------------------------------------
    /** コマンド定義 : Get                */
    const TYPE_IS_GET    = 0x01;
    /** コマンド定義 : Multi-Get          */
    const TYPE_IS_MGET   = 0x02;
    /** コマンド定義 : 新規追加           */
    const TYPE_IS_ADD    = 0x03;
    /** コマンド定義 : 更新(新規作成)     */
    const TYPE_IS_SET    = 0x04;
    /** コマンド定義 : 更新(既存データ)   */
    const TYPE_IS_REP    = 0x05;
    /** コマンド定義 : 更新(比較条件付き) */
    const TYPE_IS_CAS    = 0x06;
    /** コマンド定義 : 削除               */
    const TYPE_IS_DEL    = 0x07;
    /** コマンド定義 : 数値加算           */
    const TYPE_IS_INC    = 0x08;
    /** コマンド定義 : 数値減算           */
    const TYPE_IS_DEC    = 0x09;
    /** コマンド定義 : エラー番号         */
    const TYPE_IS_ERRNO  = 0x0A;
    /** コマンド定義 : エラーメッセージ   */
    const TYPE_IS_ERROR  = 0x0B;

    // ----[ Properties ]---------------------------------------------
    // {{{ @properties
    /**
     *  実行コマンド
     *
     *  クラス定数で定義されている抽出コマンドを指定する。
     *
     *  @var  int
     */
    protected /* int    */ $type       = NULL;

    /**
     *  データ・フォーマット・クラス名
     *
     *  データ・フォーマット・クラスを指定する。<br/>
     *  クラス定義ファイルの読み込みはクラスローダーが行う。
     *
     *  @see  Cascade_DB_System_ClassLoader
     *  @var  string
     */
    protected /* string */ $df_name    = NULL;

    /**
     *  CASトークン
     *
     *  既存のデータを比較更新するためのトークン。<br/>
     *  {@link TYPE_IS_CAS}コマンド実行時に指定する。
     *
     *  @var  string
     */
    protected /* array  */ $cas_token  = NULL;

    /**
     *  操作対象のキー
     *
     *  指定されたキーのデータ操作を行う。
     *
     *  @var  string
     */
    protected /* array  */ $key        = NULL;

    /**
     *  保存データ
     *
     *  指定のキーに保存するデータを指定する。<br/>
     *  次のコマンド実行時に指定する。
     *  {@link TYPE_IS_ADD}
     *  {@link TYPE_IS_SET}
     *  {@link TYPE_IS_REP}
     *  {@link TYPE_IS_CAS}
     *
     *  @var  string
     */
    protected /* array  */ $value      = NULL;

    /**
     *  値の加減算値
     *
     *  {@link TYPE_IS_INC}{@link TYPE_IS_DEC}コマンド実行時に
     *  加減算する値を指定する。
     *
     *  @var  int
     */
    protected /* array  */ $offset     = NULL;

    /**
     *  キー・データの有効期間
     *
     *  データに有効期間を指定する場合に指定する。
     *
     *  @var  int
     */
    protected /* array  */ $expiration = NULL;
};
