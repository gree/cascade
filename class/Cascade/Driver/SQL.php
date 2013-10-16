<?php
/**
 *  SQL.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Driver
 */

/**
 *  SQLドライバの基底インターフェース
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Driver
 */
interface Cascade_Driver_SQL extends Cascade_Driver_Driver
{
    // ----[ Class Constants ]----------------------------------------
    // {{{ FIELD DATA-INDEX
    /**
     *  ポジション
     */
    const FIELD_DEF_IS_POS        = 0x01;

    /**
     *  データ型
     */
    const FIELD_DEF_IS_TYPE       = 0x03;

    /**
     *  カラム名 (エイリアス適応後)
     */
    const FIELD_DEF_IS_NAME       = 0x02;

    /**
     *  カラム名 (エイリアス適応前)
     */
    const FIELD_DEF_IS_NAME_ORIG  = 0x05;

    /**
     *  テーブル名 (エイリアス適応後)
     */
    const FIELD_DEF_IS_TABLE      = 0x04;

    /**
     *  テーブル名 (エイリアス適応前)
     */
    const FIELD_DEF_IS_TABLE_ORIG = 0x06;
    // }}}
    // ----[ Interface Magic Methods ]--------------------------------
    // {{{ __construct
    /**
     *  コンストラクタ
     *
     *  @param  string  Database-Source-Name
     */
    public /* void */
        function __construct(/* string */ $dsn);
    // }}}
    // ----[ Interface Methods ]--------------------------------------
    // {{{ query
    /**
     *  クエリーを実行する
     *
     *  接続されたデーターベースに対して、指定されたクエリーを実行する。
     *   - クエリー文字列には、疑問符型のプレースホルダのみ利用可能
     *   - プレースホルダの数とバインドする数(配列の要素数)は一致しなければならない
     *   - バインド値のエスケープは各々のドライバー依存となる
     *
     *  @param   string   実行クエリー文字列
     *  @param   array    バインド変数値
     *  @param   boolean  すべての結果レコードを確保する
     *  @return  boolean  TRUE:正常終了
     */
    public /* void */
        function query(/* string  */ $query,
                       /* array   */ $params       = array(),
                       /* boolean */ $store_result = TRUE,
                       /* string  */ $db_name      = NULL);
    // }}}
    // {{{ select_db
    /**
     *  データベースを選択する
     *
     *  通常は接続時にデータベースを選択するので、<br/>
     *  明示的にデータベースを変更する際にのみ使用します。
     *
     *  @param  string  データベース名
     */
    public /* void */
        function select_db(/* string */ $db_name);
    // }}}
    // {{{ real_escape_string
    /**
     *  SQL文で使用する文字列の特殊文字をエスケープする
     *
     *  エンコードされる文字は NUL(ASCII 0), \n, \r, \, ', ", Control-Z です。<br/>
     *  その際、接続で使用している現在の文字セットが考慮されます。
     *
     *  @param   string  エスケープする対象の文字列
     *  @return  string  エスケープされた文字列
     */
    public /* string */
        function real_escape_string(/* string */ $escape_str);
    // }}}
    // ----[ Interface Methods ]--------------------------------------
    // {{{ affected_rows
    /**
     *  直前のクエリー操作で変更された行の数を取得する
     *
     *  返されるレコード数について
     *    - 正の値     : 変更された行、もしくは取得された行がある場合
     *    - 0          : UPDATEで更新がない、WHERE条件に当てはまらない場合
     *    - 負の値(-1) : クエリーがエラーを返した場合
     *
     *  @return  int  レコード数
     */
    public /* int */
        function affected_rows(/* void */);
    // }}}
    // {{{ last_insert_id
    /**
     *  直近のクエリで使用した自動生成IDxoを取得する
     *
     *  返される値について
     *    - 更新されたAUTO_INCREMENT値を返す
     *    - クエリがAUTO_INCREMENTの値を更新しなかった場合は0
     *    - (注意) クエリー内でLAST_INSERT_ID()関数を用いると返す値が変更される
     */
    public /* int */
        function last_insert_id(/* void */);
    // }}}
    // {{{ fetch_one
    /**
     *  1レコード目の先頭カラムの結果データを取得する
     *
     *  結果データの情報
     *   - 実行結果の1レコード目の先頭カラムのデータ
     *   - 結果レコードが無い場合はNULL
     *
     *  @return  scalar  クエリー実行結果
     */
    public /* scalar */
        function fetch_one(/* void */);
    // }}}
    // {{{ fetch
    /**
     *  結果レコードを取得する
     *
     *  結果データの情報
     *   - レコードを1行を取得
     *   - レコード・データの取得スタイルはASSOC
     *   - 結果レコードが無い場合はNULL
     *   - ポインターが終端に到着した場合はNULL
     *   - 関数が実行されると、次のレコードにポインターを進める
     *
     *  @return  array  クエリー実行結果
     */
    public /* array */
        function fetch(/* void */);
    // }}}
    // {{{ fetch_all
    /**
     *  全ての結果レコードを取得する
     *
     *  結果データの情報
     *   - 全てのレコードを取得
     *   - レコード・データの取得スタイルはASSOC
     *   - 結果レコードが無い場合は空の配列
     *
     *  @return  array  クエリー実行結果
     */
    public /* array */
        function fetch_all(/* void */);
    // }}}
    // ----[ Interface Methods ]--------------------------------------
    // {{{ fetch_field
    /**
     *  結果セットの指定カラムのフィールド情報を取得する
     *
     *  フィールド情報の種別はクラス定数で定義したものを用いる
     *   - {@link FIELD_DEF_IS_POS}        : カラム位置
     *   - {@link FIELD_DEF_IS_TYPE}       : 型情報
     *   - {@link FIELD_DEF_IS_NAME}       : カラム名
     *   - {@link FIELD_DEF_IS_NAME_ORIG}  : カラム名(エイリアス設定前)
     *   - {@link FIELD_DEF_IS_TABLE}      : テーブル名
     *   - {@link FIELD_DEF_IS_TABLE_ORIG} : テーブル名(エイリアス設定前)
     *
     *  @param   string   カラム名(エイリアス設定前のオリジナル)
     *  @param   int      フィールド情報の種別
     *  @return  array    フィールド情報
     */
    public /* scalar */
        function fetch_field(/* string */ $name,
                             /* int */    $type);
    // }}}
    // {{{ fetch_field_all
    /**
     *  結果セットの全てのフィールド情報を取得する
     *
     *  @return  array  フィールド情報を格納した配列
     */
    public /* array */
        function fetch_field_all(/* void */);
    // }}}
    // ----[ Interface Methods ]--------------------------------------
    // {{{ connect
    /**
     *  コネクションを確立する
     *
     *  コンストラクタで指定されたDSNに該当するサーバーと接続を確立する。<br/>
     *  接続が確立できない場合
     *   - 設定試行回数だけ接続を試みる
     *   - 複数台登録されている場合は、サーバを再選択する
     */
    public /* void */
        function connect(/* void */);
    // }}}
    // {{{ close
    /**
     *  コネクションを切断する
     */
    public /* void */
        function close(/* void */);
    // }}}
};
