<?php
/**
 *  SQL.php
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_DB
 *  @subpackage  SQL
 */

/**
 *  SQLテーブル情報定義クラス
 *
 *  SQLテーブル情報の各種設定値を設定する。<br />
 *  SQLのデータ取得の形式、使用するドライバーもテーブル定義毎に行う。
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_DB
 *  @subpackage  SQL
 */
abstract class Cascade_DB_SQL_DataFormat
    extends    Cascade_DB_DataFormat
{
    // ----[ Class Constants ]----------------------------------------
    /**
     *  ドライバー形式 : MySQLi
     */
    const DRIVER_MYSQLI     = Cascade::DRIVER_MYSQLI;

    /**
     *  データ取得形式 : 添字配列
     */
    const FETCH_MODE_NUM    = Cascade_DB_SQL_Statement::FETCH_MODE_NUM;

    /**
     *  データ取得形式 : 連想配列
     */
    const FETCH_MODE_ASSOC  = Cascade_DB_SQL_Statement::FETCH_MODE_ASSOC;

    // ----[ Properties ]---------------------------------------------
    // {{{ TABLE
    /**
     *  テーブル名
     *  標準で選択されるテーブル名
     *   - テーブル名のマークアップ表記(MAGIC CONTENT WARD)を置換する。
     *   - 動的にテーブル名を指定する場合、{@link getTableName()}関数を拡張する。
     *  @var string
     */
    protected $table_name        = NULL;

    /**
     *  プライマリーKEY
     *   - PRIMARY KEYがテーブル定義に存在する場合、指定する。
     *   - Multi-Column-IndexによるPRIMARY_KEYの場合は配列形式で指定する。
     *   - {@link FETCH_MODE_ASSOC}の場合に結果セットの連想配列に使用される。
     *  example :
     *  <pre>
     *    $primary_key = 'id';
     *    $primary_key = array('user_id', 'item_id');
     *  </pre>
     *  @var string|array
     */
    protected $primary_key       = NULL;

    /**
     *  データ取得KEY
     *   - {@link primary_key}を基準にデータ取得する場合はNULLを指定する。
     *   - {@link Cascade_DB_SQL_Session::get()}の関数説明を参照
     *   - {@link Cascade_DB_SQL_Session::mget()}の関数説明を参照
     *  @var string|array
     */
    protected $fetch_key         = NULL;

    /**
     *  AUTO_INCREMENT所持フラグ
     *  PRIMARY-KEYがAUTO_INCREMENTである場合TRUEを指定する。
     *  @var boolean
     */
    protected $auto_increment    = TRUE;

    /**
     *  更新日の格納カラム名
     *  レコードが更新された日付情報を格納するためのカラムを指定する。<br/>
     *  存在しない場合はNULLを指定する。<br/>
     *  example :
     *  <pre>
     *    $updated_at_column = 'mtime';
     *  </pre>
     *  @var string
     */
    protected $updated_at_column = NULL;

    /**
     *  作成日の格納カラム名
     *  レコードが作成された日付情報を格納するためのカラムを指定する。<br/>
     *  存在しない場合はNULLを指定する。<br/>
     *  example :
     *  <pre>
     *    $create_at_column = 'ctime';
     *  </pre>
     *  @var string
     */
    protected $created_at_column = NULL;

    /**
     *  テーブル・カラム名一覧
     *  SQLテーブルのカラム名一覧登録
     *  example :
     *  <pre>
     *    $field_names = array(
     *       'id',        // PRIMARY-KEY
     *       'user_id',   // ユーザID
     *       'item_id',   // アイテムID
     *    );
     *  </pre>
     *  @var array
     */
    protected $field_names       = array();
    // }}}
    // {{{ DRIVER
    /**
     *  接続ドライバー識別子
     *  SQLデータベースへ問い合わせをに用いるドライバーを指定する。
     *  @var int
     */
    protected $driver_type       = self::DRIVER_MYSQLI;

    // ----[ Properties ]---------------------------------------------
    /**
     *  結果データ取得形式
     *  SQLへの問い合わせ結果スタイルを指定する。
     *  @var int
     */
    protected $fetch_mode        = self::FETCH_MODE_ASSOC;
    // }}}
    // ----[ Properties ]---------------------------------------------
    // {{{ DATA-SOURCE-NAME
    /**
     *  マスーターDSN
     *  標準で選択される、マスターDSN
     *   - 動的にマスターDSNを指定する場合、{@link getMasterDSN()}関数を拡張する。
     *  @var string
     */
    protected $master_dsn        = NULL;

    /**
     *  スレーブDSN
     *  標準で選択される、スレーブDSN
     *   - 動的にスレーブDSNを指定する場合、{@link getSlaveDSN()}関数を拡張する。
     *  @var string
     */
    protected $slave_dsn         = NULL;

    /**
     *  拡張DSN登録
     *  拡張DSNの登録
     *   - SQLの参照系問い合わせで、拡張DSNを利用する際の識別子を登録する。
     *   - 使用しない場合、空定義でよい。
     *  example :
     *  <pre>
     *    $extra_dsn = array(
     *        'ext_01'  => 'xxx://slave_01/xxxx',
     *        'standby' => 'xxx://standby/xxxx',
     *    );
     *  </pre>
     *  @var array
     */
    protected $extra_dsn         = array();

    /**
     *  データベース名
     *  標準で選択される、データベース名
     *   - 動的にDBを指定する場合、{@link getDatabaseName()}関数を拡張する。
     *  @var string
     */
    protected $database_name     = NULL;
    // }}}
    // ----[ Properties ]---------------------------------------------
    // {{{ INTERCEPTOR
    /**
     *  割り込み処理登録
     *  割り込み処理の実装クラスの登録
     *   - 割り込み処理のインスタンスは、実行毎に新規に作成(ステートレス)される。
     *  @var array
     */
    protected $interceptors      = array(
        'Cascade_AOP_SQL_StatementCacheInterceptor',
    );
    // }}}
    // ----[ Properties ]---------------------------------------------
    // {{{ QUERY
    /**
     *  クエリー定義
     *  動的クエリーの登録
     *   - テーブル名はMAGIC CONTENT WARDを用いる。
     *   - [:変数名] のマークアップでBIND変数を定義できる。
     *   -- エスケープする場合は[\:]を用いる。
     *   - OFFSET, LIMITトークンは記述しない。{@link Cascade_DB_SQL_Criteria}
     *  example :
     *  <pre>
     *    $queries = array(
     *       'find_by_user' => array(
     *          'sql' => 'SELECT * FROM __TABLE_NAME__ WHERE user_id = :user_id',
     *       ),
     *       'init_uvar' => array(
     *          'sql' => 'SET @hogehoge \:= NULL',
     *       )
     *    );
     *  </pre>
     *  @var array
     */
    protected $queries           = array();
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ getShardSelector
    /**
     *  ShardSelectorを取得する
     *
     *  @return  Cascade_DB_SQL_ShardSelector
     */
    public /* string */
        function getShardSelector(/* void */)
    {
        return NULL;
    }
    // }}}
    // {{{ getMasterDSN
    /**
     *  マスターDSNを取得する
     *
     *  マスター・サーバーのDSNを取得する。<br/>
     *  動的にDSNを取得する場合は、抽出条件を元に決定する。
     *
     *  @param   Cascade_DB_Criteria  抽出条件
     *  @return  string               マスターDSN
     */
    public /* string */
        function getMasterDSN(Cascade_DB_Criteria $criteria)
    {
        $dsn      = $this->master_dsn;
        $selector = $this->getShardSelector();
        if ($selector) {
            $suffix = $selector->getDSNSuffix($criteria);
            if (is_string($suffix)) {
                $dsn = sprintf('%s%s', $dsn, $suffix);
            }
        }
        return $dsn;
    }
    // }}}
    // {{{ getSlaveDSN
    /**
     *  スレーブDSNを取得する
     *
     *  スレーブ・サーバーのDSNを取得する。<br/>
     *  動的にDSNを取得する場合は、抽出条件を元に決定する。
     *
     *  @param   Cascade_DB_Criteria  抽出条件
     *  @return  string               スレーブDSN
     */
    public /* string */
        function getSlaveDSN(Cascade_DB_Criteria $criteria)
    {
        $dsn      = $this->slave_dsn;
        $selector = $this->getShardSelector();
        if ($selector) {
            $suffix = $selector->getDSNSuffix($criteria);
            if (is_string($suffix)) {
                $dsn = sprintf('%s%s', $dsn, $suffix);
            }
        }
        return $dsn;
    }
    // }}}
    // {{{ getExtraDSN
    /**
     *  拡張DSNを取得する
     *
     *  マスターDSN、スレーブDSNのみでは、スケールさせるのに難しい場合に用いる。<br/>
     *  拡張DSNの決定は、抽出条件から行う。
     *  {@source}
     *
     *  @param   Cascade_DB_Criteria  抽出条件
     *  @return  string               スレーブDSN
     */
    public /* string */
        function getExtraDSN(Cascade_DB_Criteria $criteria)
    {
        if (isset($this->extra_dsn[$criteria->extra_dsn]) === FALSE) {
            return NULL;
        }
        $dsn      = $this->extra_dsn[$criteria->extra_dsn];
        $selector = $this->getShardSelector();
        if ($selector) {
            $suffix = $selector->getDSNSuffix($criteria);
            if (is_string($suffix)) {
                $dsn = sprintf('%s%s', $dsn, $suffix);
            }
        }
        return $dsn;
    }
    // }}}
    // {{{ getDatabaseName
    /**
     *  データベース名を取得する
     *
     *  任意指定されたデータベース名を取得する。<br/>
     *  動的にデータベースを切り替えて使用する場合に定義する。<br />
     *
     *  @param   Cascade_DB_Criteria  データ抽出条件
     *  @return  string               データベース名
     */
    public /* string */
        function getDatabaseName(Cascade_DB_Criteria $criteria)
    {
        return $this->database_name;
    }
    // }}}
    // {{{ getTableName
    /**
     *  テーブル名を取得する
     *
     *  クエリー文字列内ののテーブル名予約語を置換す値を取得する。<br/>
     *  テーブル情報を動的に取得する場合は、抽出条件を元にテーブル名を返す。
     *
     *  @param   Cascade_DB_Criteria  データ抽出条件
     *  @return  string               テーブル名
     */
    public /* string */
        function getTableName(Cascade_DB_Criteria $criteria)
    {
        $tb_name  = $this->table_name;
        $selector = $this->getShardSelector();
        if ($selector) {
            $suffix = $selector->getTableNameSuffix($criteria);
            if (is_string($suffix)) {
                $tb_name = sprintf('%s%s', $tb_name, $suffix);
            }
        }
        return $tb_name;
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
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
    // {{{ getPrimaryKey
    /**
     *  主キーを取得する
     *
     *  主キーとなるカラム名を取得する。<br/>
     *  複合キーの場合は、複数のカラム名を配列形式で構築する。
     *
     *  @return  string|array  主キー
     */
    public final /* mixed */
        function getPrimaryKey(/* void */)
    {
        return $this->primary_key;
    }
    // }}}
    // {{{ getFetchKey
    /**
     *  データ取得キーを取得する
     *
     *  データ取得時に主キーより優先されるキーを取得する。<br/>
     *  複合キーの場合は、複数のカラム名を配列形式で構築する。
     *  効力がある関数は
     *   - {@link Cascade_DB_SQL_Session::get()}
     *   - {@link Cascade_DB_SQL_Session::getEx()}
     *   - {@link Cascade_DB_SQL_Session::mget()}
     *   - {@link Cascade_DB_SQL_Session::mgetEx()}
     *
     *  @return  string|array  データ取得キー
     */
    public final /* mixed */
        function getFetchKey(/* void */)
    {
        return $this->fetch_key;
    }
    // }}}
    // {{{ getCardinalKey
    /**
     *  データ取得に用いるキーを決定、取得する
     *
     *  主キーと、データ取得キーの設定情報を確認後<br/>
     *  データ取得に用いるキーを決定して取得する。
     *
     *  @return  string|array  データ取得のためのKEY
     */
    public final /* mixed */
        function getCardinalKey(/* void */)
    {
        return $this->isUseFetchKey()
            ? $this->fetch_key
            : $this->primary_key;
    }
    // }}}
    // {{{ isUseFetchKey
    /**
     *  データ取得キーが設定されているか確認する
     *
     *  データ取得キーが設定されている場合はTRUEを返す。
     *
     *  @return  boolean  データ取得キーの設定有無
     */
    public final /* boolean */
        function isUseFetchKey(/* void */)
    {
        return ($this->fetch_key !== NULL) ? TRUE : FALSE;
    }
    // }}}
    // {{{ getActiveDSN
    /**
     *  接続するDSNを取得する
     *
     *  抽出条件より接続対象となるDSNを取得する。
     *  {@source 4 10}
     *
     *  @param   Cascade_DB_Criteria  抽出条件
     *  @return  string               接続対象DSN
     */
    public final /* string */
        function getActiveDSN(Cascade_DB_Criteria $criteria)
    {
        $dsn = NULL;
        if ($criteria->use_master) {
            $dsn = $this->getMasterDSN($criteria);
        } else {
            if ($criteria->extra_dsn !== NULL) {
                $dsn = $this->getExtraDSN($criteria);
            }
            if ($dsn === NULL) {
                $dsn = $this->getSlaveDSN($criteria);
            }
        }
        return $dsn;
    }
    // }}}
    // {{{ getDynamicQuery
    /**
     *  実行クエリー文字列を動的に取得する
     *
     *  抽出条件を元に実行するクエリー文字列を動的に取得する。
     *  {@source 3 3}
     *
     *  @param   Cascade_DB_Criteria  データ抽出条件
     *  @return  string               クエリー
     */
    public final /* string */
        function getDynamicQuery(Cascade_DB_Criteria $criteria)
    {
        if (isset($this->queries[$criteria->stmt_name]['sql'])) {
            return $this->queries[$criteria->stmt_name]['sql'];
        }
        $callback = self::getExtraStaticProperty(self::EXTRA_STATIC_PROP_DQ_CALLBACK);
        if (is_callable($callback)) {
            if (strlen($query = call_user_func($callback, $this, $criteria))) {
                return $query;
            }
        }
        $ex_msg  = 'Undefined dynamic query {df, stmt_name} %s %s';
        $ex_msg  = sprintf($ex_msg, $criteria->df_name, $criteria->stmt_name);
        throw new Cascade_Exception_DBException($ex_msg);
    }
    // }}}
    // {{{ isDisableCacheMode
    /**
     *  キャッシュ機能がOFFモードかを確認する
     *
     *  @return  boolean  キャッシュ機能がOFFモード
     */
    public /* boolean */
        function isDisableCacheMode(Cascade_DB_Criteria $criteria)
    {
        if (@$this->queries[$criteria->stmt_name][self::EXTRA_PROP_NO_CACHE]) {
            return true;
        }
        return parent::isDisableCacheMode($criteria);
    }
    // }}}
};