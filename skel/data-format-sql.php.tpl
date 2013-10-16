<?php
/**
 *  ファイルの説明文を書く
 *
 *  @package  {$package}
 *  @author   {$author}
 */

/**
 *  クラスの説明文を書く
 *
 *  クラスのプロパティ値の説明 :: 基本パラメーター
 *      +--------------------+---------+------------------------------------------------+
 *      | 変数               | 型      | 説明                                           |
 *      +====================+=========+================================================+
 *      | $table_name        | string  | テーブル名                                     |
 *      +--------------------+---------+------------------------------------------------+
 *      | $primary_key       | string  | PRIMARY-KEY   (multi-column-indexは配列で定義) |
 *      +--------------------+---------+------------------------------------------------+
 *      | $auto_increment    | boolean | AUTO_INCREMENTフラグ                           |
 *      +--------------------+---------+------------------------------------------------+
 *      | $master_dsn        | string  | マスターDSN                                    |
 *      +--------------------+---------+------------------------------------------------+
 *      | $slave_dsn         | string  | スレーブDSN                                    |
 *      +--------------------+---------+------------------------------------------------+
 *      | $extra_dsn         | array   | 拡張DSNリスト                                  |
 *      +--------------------+---------+------------------------------------------------+
 *      | $field_names       | array   | フィールド名リスト                             |
 *      +--------------------+---------+------------------------------------------------+
 *      | $queries           | array   | クエリ定義                                     |
 *      +--------------------+---------+------------------------------------------------+
 *
 *  クラスのプロパティ値の説明 :: 拡張系パラメーター
 *      +--------------------+---------+------------------------------------------------+
 *      | 変数               | 型      | 説明                                           |
 *      +====================+=========+================================================+
 *      | $fetch_key         | string  | データ取得KEY (指定しない場合は$primary_key)   |
 *      +--------------------+---------+------------------------------------------------+
 *      | $driver_type       | int     | DB接続ドライバー種別                           |
 *      +--------------------+---------+------------------------------------------------+
 *      | $fetch_mode        | int     | 結果のフェッチモード                           |
 *      +--------------------+---------+------------------------------------------------+
 *
 *  @package  {$package}
 *  @author   {$author}
 */
class {$class_name}
    extends Cascade_DB_SQL_DataFormat
{ldelim}
    // ------------------------ Attributes ---------------------------
    protected /* string  */ $table_name     = '{$table_name}';
    protected /* string  */ $primary_key    = '{$primary_key|default:'id'}';
    protected /* boolean */ $auto_increment =  {$auto_increment|default:'TRUE'};
    protected /* string  */ $master_dsn     = 'gree://master/{$dsn_ident}';
    protected /* string  */ $slave_dsn      = 'gree://slave/{$dsn_ident}';
    protected /* array   */ $extra_dsn      = array(
        'standby' => 'gree://standby/{$dsn_ident}',
    );
    protected /* array   */ $field_names    = array(
{foreach from=$field_names item=name}
    {assign var="output" value="'`$name`',"}
        {$output|str_pad:$field_name_max_len+10} // 説明
{/foreach}
    );
    protected /* array   */ $queries        = array(
        'sample' => array(
            'sql' => 'SELECT * FROM __TABLE_NAME__',
        ),
    );
{rdelim};
