<?php
/**
 *  MySQLi.php
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_Driver
 *  @subpackage  SQL
 */

/**
 *  Cascade_Driver_SQL_MySQLi
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_Driver
 *  @subpackage  SQL
 */
final class    Cascade_Driver_SQL_MySQLi
    extends    Cascade_Driver_SQL_Common
    implements Cascade_Driver_SQL
{
    // ----[ Properties ]---------------------------------------------
    // {{{ SERVER
    /**
     *  パース処理されたDSN情報
     *  @var string
     */
    protected $dsn            = NULL;

    /**
     *  接続対象ホストの一覧
     *  @var  array
     */
    protected $hosts          = array();

    /**
     *  接続試行回数の最大値
     *  @var  int
     */
    protected $max_retry_conn = 10;
    // }}}
    // ----[ Properties ]---------------------------------------------
    // {{{ OPERATION
    /**
     *  実行クエリー
     *  @var  string
     */
    protected $query          = NULL;

    /**
     *  クエリー実行時間
     *  @var  int
     */
    protected $elapsed_time   = 0;

    /**
     *  クエリー実行による操作レコード数
     *  @var  int
     */
    protected $affected_rows  = 0;

    /**
     *  直近のクエリで使用した自動生成ID
     *  @var  int
     */
    protected $last_insert_id = 0;

    /**
     *  クエリー実行結果フィールド情報
     *  @var  array
     */
    protected $def_fields     = array();
    // }}}
    // ----[ Properties ]---------------------------------------------
    // {{{ RESOURCE
    /**
     *  接続リソース
     *  @var  resource
     */
    protected $le_link        = NULL;

    /**
     *  クエリー実行結果リソース
     *  @var  resource
     */
    protected $le_result      = NULL;
    // }}}
    // ----[ Properties ]---------------------------------------------
    // {{{ ERROR
    /**
     *  エラー・コード
     *  @var  int
     */
    protected $error_code     = 0;

    /**
     *  エラー・メッセージ
     *  @var  string
     */
    protected $error_message  = NULL;
    // }}}
    // ----[ Magic Methods ]------------------------------------------
    // {{{ __construct
    /**
     *  コンストラクタ
     *
     *  @param  string  Database-Source-Name
     */
    public /* void */
        function __construct(/* string */ $dsn)
    {
        // 親コンストラクタの呼び出し
        parent::__construct($dsn);
        // MySQLiのエラー情報処理を設定
        mysqli_report(MYSQLI_REPORT_ERROR);
        // DSNのパース処理
        $this->dsn = Cascade_System_DSN::parse($dsn);
        // 接続処理
        $this->connect();
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ is_enable
    /**
     *  利用可能なドライバかを確認する
     *
     *  PHPの拡張モジュールの読み込み状態や、<br />
     *  バージョン情報を考慮しドライバの有効の有無を判断する
     *
     *  @return  boolean  TRUE:利用可能ドライバ
     */
    public static /* boolean */
        function is_enable(/* boolean */ $notice = TRUE)
    {
        // 利用可能条件
        $extension_name   = 'mysqli';
        $required_version = '0.1';

        // ドライバの有効確認
        if (extension_loaded($extension_name)) {
            $extension_version = phpversion($extension_name);
            if (version_compare($extension_version, $required_version, '>=')) {
                return TRUE;
            }
        }
        // 警告表示
        $error = 'Disable extension module {name, version} %s (%s<=)';
        $error = sprintf($error, $extension_name, $required_version);
        if ($notice) {
            trigger_error($error, E_USER_NOTICE);
        }
        return FALSE;
    }
    // }}}
    // {{{ get_version
    /**
     *  ドライバーのバージョン情報を取得する
     *
     *  @return  int  バージョン情報
     */
    public static /* string */
        function get_version(/* void */)
    {
        $extension_name = 'mysqli';
        return phpversion($extension_name);
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ get_error_code
    /**
     *  エラー番号を取得する
     *
     *  @return  int  エラー番号
     */
    public /* int */
        function get_error_code(/* void */)
    {
        return $this->error_code;
    }
    // }}}
    // {{{ get_error_message
    /**
     *  エラーメッセージを取得する
     *
     *  @return  string  エラーメッセージ
     */
    public /* string */
        function get_error_message(/* void */)
    {
        return $this->error_message
            ?  $this->get_log_message('ERROR')
            :   NULL;
    }
    // }}}
    // {{{ php_error_handler
    /**
     *  エラーハンドリング
     *
     *  @param   int      エラー・レベル
     *  @param   string   エラー・メッセージ
     *  @param   string   ファイル名
     *  @param   int      行番号
     *  @return  boolean  TRUE : PHPの内部エラーハンドラを実行しない
     */
    protected /* void */
        function php_error_handler(/* int    */ $errno,
                                   /* string */ $error,
                                   /* string */ $file,
                                   /* int    */ $line)
    {
        $this->error_code    = $errno;
        $this->error_message = $error;
        trigger_error($this->get_log_message('ERROR'), E_USER_NOTICE);
        return TRUE;
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
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
     *  @param   string   任意のDBを選択する場合に指定する
     *  @return  boolean  TRUE:正常終了
     */
    public /* boolean */
        function query(/* string  */ $query,
                       /* array   */ $params       = array(),
                       /* boolean */ $store_result = TRUE,
                       /* string  */ $db_name      = NULL)
    {
        // 接続状態を確認する
        if ($this->le_link === NULL
            || mysqli_ping($this->le_link) === FALSE) {
            return FALSE;
        }

        // 内部変数の初期化
        $this->le_result     = NULL;
        $this->affected_rows = 0;
        $this->elapsed_time  = 0;
        $this->query         = NULL;
        $this->def_fields    = array();
        $this->error_code    = 0;
        $this->error_message = NULL;

        // データベースの切り替え
        if ($this->select_db($db_name) === FALSE) {
            return FALSE;
        }

        // クエリーの実行処理
        $query      = $this->get_emulate_query($query, $params);
        $s_time     = microtime(TRUE);
        set_error_handler(array($this, 'php_error_handler'));
        $is_success = mysqli_real_query($this->le_link, $query);
        restore_error_handler();
        $e_time     = microtime(TRUE);

        $this->query          = $query;
        $this->elapsed_time   = $e_time - $s_time;
        $this->affected_rows  = mysqli_affected_rows($this->le_link);
        $this->last_insert_id = mysqli_insert_id($this->le_link);

        // 実行結果を確認する
        if ($is_success) {
            $this->use_or_store_result($store_result);
        }
        $this->log();
        return $is_success;
    }
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
        function select_db(/* string */ $db_name)
    {
        // データベース名の確認
        if (strlen($db_name) == 0
            || $this->dsn['database'] == $db_name) {
            return TRUE;
        }
        // 接続状態を確認する
        if ($this->le_link === NULL
            || mysqli_ping($this->le_link) === FALSE) {
            return FALSE;
        }
        // データベースを選択処理
        set_error_handler(array($this, 'php_error_handler'));
        $selected = mysqli_select_db($this->le_link, $db_name);
        restore_error_handler();
        if ($selected) {
            $this->dsn['database'] = $db_name;
        }
        // 結果を返す
        return $selected ? TRUE : FALSE;
    }
    // }}}
    // {{{ get_emulate_query
    /**
     *  実行可能なクエリー文字列を取得する
     *
     *  バインド処理をエミュレーションする
     *
     *  @param   string  クエリー文字列
     *  @param   array   (optional) バインド変数値
     *  @return  string  実行可能なクエリー文字列
     */
    protected /* string */
        function get_emulate_query(/* string */ $query,
                                   /* array  */ $params = array())
    {
        $pos           = 0;
        $emulate_query = '';
        $tmp = preg_split('/((?<!\\\)[&?!])/', $query, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($tmp as $_token) {
            switch ($_token) {
            case '?':
                // バインド値の有無確認
                if (array_key_exists($pos, $params) === FALSE) {
                    $error = "Doesn't match count of bind-value {query, pos} %s %d";
                    $error = sprintf($error, $query, $pos);
                    $this->error_code    = -1;
                    $this->error_message = $error;
                    return FALSE;
                }
                // プレースホルダを値に置き換える
                if ($params[$pos] === null) {
                    $emulate_query .= 'NULL';
                } else {
                    $emulate_query .= is_string($params[$pos])
                        ? sprintf("'%s'", $this->real_escape_string($params[$pos]))
                        : $params[$pos];
                }
                $pos ++;
                break;
            default:
                $emulate_query .= preg_replace('/\\\([&?!])/', "\\1", $_token);
                break;
            }
        }
        return $emulate_query;
    }
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
        function real_escape_string(/* string */ $escape_str)
    {
        // 接続リソースが確保されていない場合は、そのまま返す
        if ($this->le_link === NULL) {
            return $escape_str;
        }
        // エスケープ処理
        return mysqli_real_escape_string($this->le_link, $escape_str);
    }
    // }}}
    // {{{ use_or_store_result
    /**
     *  結果データを構築する
     *
     *  結果セットを取得する
     *    - 一括、逐次読み込みを任意に設定する場合は引数で指定する
     *    - デフォルトは一括読み込みとする
     *
     *  @param   boolean  結果セットを一括読み込みする場合TRUE
     *  @retrun  boolean  TRUE:成功時
     */
    protected /* boolean */
        function use_or_store_result(/* boolean */ $store_result)
    {
        // 結果データを返さない場合はそのまま終了
        if (mysqli_field_count($this->le_link) < 1) {
            return TRUE;
        }
        // 結果データを取得
        set_error_handler(array($this, 'php_error_handler'));
        $le_result = ($store_result)
            ? mysqli_store_result($this->le_link)
            : mysqli_use_result($this->le_link);
        restore_error_handler();
        if ($le_result === FALSE) {
            return FALSE;
        }
        // フィールド情報を取得する
        foreach (mysqli_fetch_fields($le_result) as $field) {
            $tmp = array(
                self::FIELD_DEF_IS_POS        => count($this->def_fields),
                self::FIELD_DEF_IS_TYPE       => $field->type,
                self::FIELD_DEF_IS_NAME       => $field->name,
                self::FIELD_DEF_IS_NAME_ORIG  => $field->orgname,
                self::FIELD_DEF_IS_TABLE      => $field->table,
                self::FIELD_DEF_IS_TABLE_ORIG => $field->orgtable,
            );
            // MEMO : 底層レベルのバグでorgnameが取得できない場合がある
            if (strlen($field->orgname) < 1) {
                $this->def_fields[$field->name]    = $tmp;
            } else {
                $this->def_fields[$field->orgname] = $tmp;
            }
        }
        $this->le_result     = $le_result;
        $this->affected_rows = mysqli_num_rows($le_result);
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
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
        function affected_rows(/* void */)
    {
        return $this->affected_rows;
    }
    // }}}
    // {{{ last_insert_id
    /**
     *  直近のクエリで使用した自動生成IDを取得する
     *
     *  返される値について
     *    - 更新されたAUTO_INCREMENT値を返す
     *    - クエリがAUTO_INCREMENTの値を更新しなかった場合は0
     *    - (注意) クエリー内でLAST_INSERT_ID()関数を用いると返す値が変更される
     */
    public /* int */
        function last_insert_id(/* void */)
    {
        return $this->last_insert_id;
    }
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
        function fetch_one(/* void */)
    {
        // 結果リソースを確認する
        if ($this->le_result === NULL) {
            return NULL;
        }

        // 結果データを取得
        set_error_handler(array($this, 'php_error_handler'));
        $row  = mysqli_fetch_row($this->le_result);
        restore_error_handler();
        $data = array_key_exists(0, $row) ? $row[0] : NULL;

        // 結果リソースの解放
        mysqli_free_result($this->le_result);
        $this->le_result = NULL;

        // 取得結果を返す
        return $data;
    }
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
        function fetch(/* void */)
    {
        // 結果リソースを確認する
        if ($this->le_result === NULL) {
            return NULL;
        }

        // 結果データを取得
        set_error_handler(array($this, 'php_error_handler'));
        $row = mysqli_fetch_assoc($this->le_result);
        restore_error_handler();

        // 結果リソースの解放
        if ($row === NULL) {
            mysqli_free_result($this->le_result);
            $this->le_result = NULL;
        }

        // 取得結果を返す
        return $row;
    }
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
        function fetch_all(/* void */)
    {
        $rows = array();
        while ($tmp = $this->fetch()) {
            $rows[] = $tmp;
        }
        return $rows;
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
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
                             /* int */    $type)
    {
        $data = isset($this->def_fields[$name][$type])
            ? $this->def_fields[$name][$type]
            : NULL;
        return $data;
    }
    // }}}
    // {{{ fetch_field_all
    /**
     *  結果セットの全てのフィールド情報を取得する
     *
     *  @return  array  フィールド情報を格納した配列
     */
    public /* array */
        function fetch_field_all(/* void */)
    {
        return $this->def_fields;
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ begin
    /**
     *  トランザクションを開始する
     */
    public /* void */
        function begin(/* void */)
    {
        // 接続状態を確認する
        if ($this->le_link === NULL
            || mysqli_ping($this->le_link) === FALSE) {
            return FALSE;
        }
        // 自動コミットをオフ (トランザクション開始)
        $this->le_result     = NULL;
        $this->affected_rows = 0;
        $this->elapsed_time  = 0;
        $this->query         = 'START TRANSACTION';
        $this->def_fields    = array();
        $is_success = mysqli_autocommit($this->le_link, FALSE);
        // ログ記録
        $this->log();
        return $is_success;
    }
    // }}}
    // {{{ commit
    /**
     *  トランザクションをコミットする
     */
    public /* void */
        function commit(/* void */)
    {
        // 接続状態を確認する
        if ($this->le_link === NULL
            || mysqli_ping($this->le_link) === FALSE) {
            return FALSE;
        }
        // コミット
        $this->le_result     = NULL;
        $this->affected_rows = 0;
        $this->elapsed_time  = 0;
        $this->query         = 'COMMIT';
        $this->def_fields    = array();
        $this->error_code    = 0;
        $this->error_message = NULL;
        $is_success = mysqli_commit($this->le_link);
        mysqli_autocommit($this->le_link, TRUE);
        $this->log();
        return $is_success;
    }
    // }}}
    // {{{ rollback
    /**
     *  トランザクションをロールバックする
     */
    public /* void */
        function rollback(/* void */)
    {
        // 接続状態を確認する
        if ($this->le_link === NULL
            || mysqli_ping($this->le_link) === FALSE) {
            return FALSE;
        }
        // ロールバック
        $this->le_result     = NULL;
        $this->affected_rows = 0;
        $this->elapsed_time  = 0;
        $this->query         = 'ROLLBACK';
        $this->def_fields    = array();
        $this->error_code    = 0;
        $this->error_message = NULL;
        $is_success = mysqli_rollback($this->le_link);
        mysqli_autocommit($this->le_link, TRUE);
        $this->log();
        return $is_success;
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
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
        function connect(/* void */)
    {
        // 接続失敗時の接続試行回数を設定する
        $loop_count = $this->max_retry_conn;

        // 接続確立処理
        do {
            // -- 接続ホストを確定 ---------------
            $pos      = array_rand($this->dsn['extra']['hostspec']);
            $hostspec = $this->dsn['extra']['hostspec'][$pos];
            list($host, $port) = (strpos($hostspec, ':') !== FALSE)
                ? explode(':', $hostspec)
                : array($hostspec, NULL);
            $this->dsn['host'] = $host;
            $this->dsn['port'] = $port;

            // -- 接続処理 -----------------------
            set_error_handler(array($this, 'php_error_handler'));
            $le_link    = mysqli_init();
            $is_connect = mysqli_real_connect(
                $le_link,
                $this->dsn['host'],
                $this->dsn['user'],
                $this->dsn['pass'],
                $this->dsn['database'],
                $this->dsn['port']
            );
            if ($is_connect && strlen($this->dsn['database'])) {
                $is_connect = mysqli_select_db($le_link, $this->dsn['database']);
            }
            restore_error_handler();

            // -- 接続失敗時は再試行処理 ---------
            if ($is_connect === FALSE) {
                continue;
            }

            // -- 内部変数への保存 ---------------
            $this->error_code    = 0;
            $this->error_message = NULL;
            $this->le_link       = $le_link;
            break;
        } while (0 < --$this->max_retry_conn);

        // 接続成功時にTRUEを返す
        return ($this->error_code) ? FALSE : TRUE;
    }
    // }}}
    // {{{ close
    /**
     *  コネクションを切断する
     */
    public /* void */
        function close(/* void */)
    {
        // 接続を閉じる
        if ($this->le_link) {
            mysqli_close($this->le_link);
        }
        // 結果リソースを解放
        if ($this->le_result) {
            mysqli_free_result($this->le_result);
        }
        // 内部変数を初期化
        $this->le_link   = NULL;
        $this->le_result = NULL;
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ log
    /**
     *  ドライバ・ログを記録する
     */
    protected /* void */
        function log(/* void */)
    {
        // ログを記録
        if ($this->error_code || $this->error_message) {
            self::get_logger()->err(
                $this->get_log_message('ERROR'));
        } else {
            self::get_logger()->debug(
                $this->get_log_message('DEBUG'));
        }
    }
    // }}}
    // {{{ get_log_message
    /**
     *  ログ・メッセージを構築/取得する
     *
     *  @return  string  ログ・メッセージ
     */
    private /* string */
        function get_log_message(/* string */ $type = 'ERROR')
    {
        // Ethna Action Name
        $action = 'none';
        if (isset($_REQUEST['mode']) && isset($_REQUEST['act'])) {
            $action = $_REQUEST['mode'].'_'.$_REQUEST['act'];
        } else if (isset($_REQUEST['mode'])) {
            $action = $_REQUEST['mode'];
        } else if (isset($_REQUEST['act'])) {
            $action = $_REQUEST['act'];
        }
        // 接続先情報
        $host = $this->dsn['host'];
        $port = (int) $this->dsn['port'];
        if ($port != 0 && $port != 3306) {
            $host = sprintf('%s:%s', $host, $port);
        }
        // ログを記録
        $attr_data = array();
        switch ($type) {
        case 'ERROR':
            $attr_data = array(
                $action,
                get_class($this),
                $this->dsn['string'].'@'.$host,
                number_format($this->elapsed_time, 6),
                $this->error_code,
                $this->error_message,
                $this->query,
            );
            break;
        case 'DEBUG':
            $attr_data = array(
                $action,
                get_class($this),
                $this->dsn['string'].'@'.$host,
                number_format($this->elapsed_time, 6),
                $this->affected_rows,
                $this->query,
            );
            break;
        default:
        }
        return implode("\t", $attr_data);
    }
    // }}}
};
