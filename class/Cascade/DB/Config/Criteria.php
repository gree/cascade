<?php
/**
 *  Criteria.php
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_DB
 *  @subpackage  Config
 */

/**
 *  設定データ抽出/実行条件の定義クラス
 *
 *  設定ファイルからデータ抽出を定義するクラス
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_DB
 *  @subpackage  Config
 */
final class Cascade_DB_Config_Criteria
    extends Cascade_DB_Criteria
{
    // ----[ Class Constants ]----------------------------------------
    /** コマンド定義 : データ抽出 */
    const TYPE_IS_GET_ALL = 0x01;
    /** コマンド定義 : データ抽出 */
    const TYPE_IS_GET     = 0x02;
    /** コマンド定義 : 件数取得   */
    const TYPE_IS_COUNT   = 0x03;

    // ----[ Properties ]---------------------------------------------
    /**
     *  実行コマンド
     *
     *  クラス定数で定義されている抽出コマンドを指定する
     *  @var  int
     */
    protected /* int    */ $type    = NULL;

    /**
     *  データ・フォーマット・クラス名
     *
     *  @var  string
     */
    protected /* string */ $df_name = NULL;

    /**
     *  セクション
     *
     *  抽出するデータ値のセクションを指定する
     *  @var  array
     */
    protected /* array  */ $section = NULL;

    /**
     *  変数名
     *
     *  抽出するデータ値の変数名を指定する
     *  @var  mixed
     */
    protected /* array  */ $name    = NULL;
};