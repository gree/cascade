<?php
/**
 *  APC.php
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_Driver
 *  @subpackage  KVS
 */

/**
 *  APCによるデータ操作処理
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_Driver
 *  @subpackage  KVS
 */
class Cascade_Driver_KVS_APC
    extends    Cascade_Driver_KVS_Common
    implements Cascade_Driver_KVS
{
    // ----[ Class Constants ]----------------------------------------
    // {{{ MEMCACHED RESPONSE CODE
    const RES_SUCCESS              = 0;
    const RES_FAILURE              = 1;
    const RES_UNSUPPORTED_FUNCTION = 2;
    const RES_DATA_EXISTS          = 3;
    const RES_NOTFOUND             = 4;
    const RES_BAD_KEY_PROVIDED     = 5;
    // }}}
    // ----[ Properties ]---------------------------------------------
    // {{{ OPERATION
    /**
     *  名前空間
     *  @var  string
     */
    protected $namespace       = NULL;

    /**
     *  格納KEY
     *  @var  string
     */
    protected $storage_key     = NULL;

    /**
     *  処理種別
     *  @var  string
     */
    protected $operation       = NULL;

    /**
     *  実行時間
     *  @var  int
     */
    protected $elapsed_time    = 0;
    // }}}
    // ----[ Properties ]---------------------------------------------
    // {{{ ERROR
    /**
     *  エラー・コード
     *  @var  int
     */
    protected $error_code      = 0;

    /**
     *  エラー・メッセージ
     *  @var  string
     */
    protected $error_message   = NULL;
    // }}}
    // ----[ Magic Methods ]------------------------------------------
    // {{{ __construct
    /**
     *  コンストラクタ
     *
     *  @param  string   ネームスペース
     *  @param  string   (optional) Database-Source-Name
     *  @param  boolean  (optional) 圧縮フラグ
     */
    public /* void */
        function __construct(/* string  */ $namespace,
                             /* string  */ $dsn        = NULL,
                             /* boolean */ $compressed = FALSE)
    {
        parent::__construct($namespace, $dsn, $compressed);
        $this->namespace = $namespace;
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
     *  @param   boolean  (optional) TRUE:警告通知を出す
     *  @return  boolean  TRUE:利用可能ドライバ
     */
    public static /* boolean */
        function is_enable(/* boolean */ $notice = TRUE)
    {
        $extension_name   = 'apc';
        $required_version = '3.1.4';

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
        $extension_name = 'apc';
        return phpversion($extension_name);
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ gen_storage_key
    /**
     *  ストレージKEYを生成する
     *
     *  指定KEYに名前空間を考慮したストレージKEYを生成する。<br/>
     *  KVS上でのKEYはこの生成されたKEYを使う。
     *
     *  @param   string  KEY
     *  @return  string  名前空間を考慮したストレージKEY
     */
    protected final /* string */
        function gen_storage_key(/* string */ $key)
    {
        // 空白文字列を除去する
        $key = preg_replace('/\s/', '', $key);
        // 指定されたKEYが空でないかを確認
        if (strlen($key) < 1) {
            $error = 'Invalid a key value (empty) {namespace} %s';
            $this->error_code    = self::RES_BAD_KEY_PROVIDED;
            $this->error_message = sprintf($error, $this->namespace);
            return FALSE;
        }
        // 意図しない文字が含まれていないか確認
        if (strpos('::', $key) !== FALSE) {
            $error = 'Invalid a key value (include special character) {namespace, key} %s %s';
            $this->error_code    = self::RES_BAD_KEY_PROVIDED;
            $this->error_message = sprintf($error, $this->namespace, $key);
            return FALSE;
        }
        // ストレージKEYを生成する
        $storage_key = sprintf('%s::%s', $this->namespace, $key);
        if (strlen($storage_key) > 255) {
            $error = 'Invalid a key value (too long) {namespace, key, storage_key} %s %s %s';
            $this->error_code    = self::RES_BAD_KEY_PROVIDED;
            $this->error_message = sprintf($error, $this->namespace, $key, $storage_key);
            return FALSE;
        }
        // ストレージKEYを返す
        return $storage_key;
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
        return $this->error_message;
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ get
    /**
     *  指定KEYの値を取得する
     *
     *  指定のKEYに格納された値を取得する。<br/>
     *  見つかったデータに関してはCASトークンの情報を取得することができます。
     *
     *  @param   string  KEY
     *  @param   string  (optional) CASトークン
     *  @return  mixed   指定KEYに格納された値を取得する。
     *                   存在しない、もしくはエラーの場合はFALSEを返す。
     */
    public /* void */
        function get(/* string */  $key,
                     /* scalar */ &$cas_token = NULL)
    {
        // 変数の初期化
        $is_success  = TRUE;
        $value       = FALSE;
        $start_time  = microtime(TRUE);
        $this->error_code    = 0;
        $this->error_message = NULL;

        // ストレージKEYの生成
        $storage_key = $this->gen_storage_key($key);
        if ($storage_key === FALSE) {
            $is_success = FALSE;
        }

        // 格納データを取得
        if ($is_success) {
            $value = apc_fetch($storage_key, $is_success);
            if ($is_success === FALSE) {
                if (apc_exists($storage_key) === FALSE) {
                    $error = 'Not found stored value {namespace, key} %s %s';
                    $this->error_code    = self::RES_NOTFOUND;
                    $this->error_message = sprintf($error, $this->namespace, $key);
                } else {
                    $error = 'Faild to get stored value {namespace, key} %s %s';
                    $this->error_code    = self::RES_FAILURE;
                    $this->error_message = sprintf($error, $this->namespace, $key);
                }
            }
        }

        // ログを記録する
        $this->operation    = __FUNCTION__;
        $this->storage_key  = $storage_key;
        $this->elapsed_time = microtime(TRUE) - $start_time;
        $this->log();

        // 結果を返す
        return ($is_success) ? $value : FALSE;
    }
    // }}}
    // {{{ mget
    /**
     *  複数の指定KEYの値を取得する
     *
     *  {@link get()}関数と挙動が似ていますが、<br />
     *  複数のKEYを配列型で同時に指定する事ができます。<br />
     *  見つかったデータに関してはCASトークンの情報を同時に取得することができます。
     *
     *  @param   array   KEYリスト
     *  @param   array   (optional) CASトークン・リスト
     *  @return  mixed   指定KEYに格納された値を複数取得する。
     *                   存在しない、もしくはエラーの場合はFALSEを返す。
     */
    public /* void */
        function mget(/* array */  $keys,
                      /* array */ &$cas_tokens = array())
    {
        // 変数の初期化
        $is_success  = TRUE;
        $values      = FALSE;
        $start_time  = microtime(TRUE);
        $this->error_code    = 0;
        $this->error_message = NULL;

        // ストレージKEYの生成
        $storage_keys = array();
        foreach ($keys as $key) {
            if (FALSE === ($storage_key = $this->gen_storage_key($key))) {
                $is_success = FALSE;
                break;
            }
            $storage_keys[$key] = $storage_key;
        }

        // 格納データを取得
        if ($is_success) {
            foreach ($storage_keys as $key => $storage_key) {
                $value = apc_fetch($storage_key, $is_success);
                if ($is_success) {
                    $values[$key] = $value;
                } else if (apc_exists($storage_key) === FALSE) {
                    $is_success   = TRUE;
                } else {
                    $error = 'Faild to get stored value {namespace, key} %s %s';
                    $this->error_code    = self::RES_FAILURE;
                    $this->error_message = sprintf($error, $this->namespace, $key);
                    break;
                }
            }
        }

        // ログを記録する
        $this->operation    = __FUNCTION__;
        $this->storage_key  = $storage_keys;
        $this->elapsed_time = microtime(TRUE) - $start_time;
        $this->log();

        // 結果を返す
        return ($is_success) ? $values : FALSE;
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ add
    /**
     *  新規データを追加する
     *
     *  指定のKEYに新規データを追加する。<br/>
     *  既にデータが存在した場合は、処理に失敗しFALSEを返す。
     *
     *  @param   string   KEY
     *  @param   mixed   新規追加する値
     *  @param   int      (optional) データ有効期間
     *  @return  boolean  処理結果
     */
    public /* void */
        function add(/* string */ $key,
                     /* mixed  */ $value,
                     /* int    */ $expiration = NULL)
    {
        // 変数の初期化
        $is_success  = TRUE;
        $start_time  = microtime(TRUE);
        $this->error_code    = 0;
        $this->error_message = NULL;

        // ストレージKEYの生成
        $storage_key = $this->gen_storage_key($key);
        if ($storage_key === FALSE) {
            $is_success = FALSE;
        }

        // 追加処理
        if ($is_success) {
            if (apc_exists($storage_key) === FALSE) {
                $is_success = apc_store($storage_key, $value, $expiration);
                if ($is_success === FALSE) {
                    $error = 'Faild to store value {namespace, key} %s %s';
                    $this->error_code    = self::RES_FAILURE;
                    $this->error_message = sprintf($error, $this->namespace, $key);
                }
            } else {
                $is_success = FALSE;
                $error = 'Already exists key {namespace, key} %s %s';
                $this->error_code    = self::RES_DATA_EXISTS;
                $this->error_message = sprintf($error, $this->namespace, $key);
            }
        }

        // ログを記録する
        $this->operation    = __FUNCTION__;
        $this->storage_key  = $storage_key;
        $this->elapsed_time = microtime(TRUE) - $start_time;
        $this->log();

        // 結果を返す
        return $is_success;
    }
    // }}}
    // {{{ set
    /**
     *  データを更新する
     *
     *  指定のKEYのデータを更新します。<br/>
     *  データが存在しない場合は、新規にデータを作成します。
     *
     *  @param   string   KEY
     *  @param   mixed   新規追加する値
     *  @param   int      (optional) データ有効期間
     *  @return  boolean  処理結果
     */
    public /* void */
        function set(/* string */ $key,
                     /* mixed  */ $value,
                     /* int    */ $expiration = NULL)
    {
        // 変数の初期化
        $is_success  = TRUE;
        $start_time  = microtime(TRUE);
        $this->error_code    = 0;
        $this->error_message = NULL;

        // ストレージKEYの生成
        $storage_key = $this->gen_storage_key($key);
        if ($storage_key === FALSE) {
            $is_success = FALSE;
        }

        // 更新処理
        if ($is_success) {
            $is_success = apc_store($storage_key, $value, $expiration);
            if ($is_success === FALSE) {
                $error = 'Faild to store value {namespace, key} %s %s';
                $this->error_code    = self::RES_FAILURE;
                $this->error_message = sprintf($error, $this->namespace, $key);
            }
        }

        // ログを記録する
        $this->operation    = __FUNCTION__;
        $this->storage_key  = $storage_key;
        $this->elapsed_time = microtime(TRUE) - $start_time;
        $this->log();

        // 結果を返す
        return $is_success;
    }
    // }}}
    // {{{ replace
    /**
     *  既存データを更新する
     *
     *  指定KEYに既にあるデータを更新します。<br/>
     *  データが存在しない場合は、処理は失敗しFALSEを返します。
     *
     *  @param   string   KEY
     *  @param   mixed   新規追加する値
     *  @param   int      (optional) データ有効期間
     *  @return  boolean  処理結果
     */
    public /* void */
        function replace(/* string */ $key,
                         /* mixed  */ $value,
                         /* int    */ $expiration = NULL)
    {
        // 変数の初期化
        $is_success  = TRUE;
        $start_time  = microtime(TRUE);
        $this->error_code    = 0;
        $this->error_message = NULL;

        // ストレージKEYの生成
        $storage_key = $this->gen_storage_key($key);
        if ($storage_key === FALSE) {
            $is_success = FALSE;
        }

        // 更新処理
        if ($is_success) {
            if (apc_exists($storage_key)) {
                $is_success = apc_store($storage_key, $value, $expiration);
                if ($is_success === FALSE) {
                    $error = 'Faild to store value {namespace, key} %s %s';
                    $this->error_code    = self::RES_FAILURE;
                    $this->error_message = sprintf($error, $this->namespace, $key);
                }
            } else {
                $is_success = FALSE;
                $error = 'Not found key {namespace, key} %s %s';
                $this->error_code    = self::RES_NOTFOUND;
                $this->error_message = sprintf($error, $this->namespace, $key);
            }
        }

        // ログを記録する
        $this->operation    = __FUNCTION__;
        $this->storage_key  = $storage_key;
        $this->elapsed_time = microtime(TRUE) - $start_time;
        $this->log();

        // 結果を返す
        return $is_success;
    }
    // }}}
    // {{{ cas
    /**
     *  既存データ状態を確認して更新する
     *
     *  サーバ上の既存データのCASトークンの値と、<br/>
     *  更新処理時に渡されたクライアントのCASトークンを比較して、<br/>
     *  一致すればデータを更新します。一致しない場合は処理は失敗しFALSEを返します。
     *
     *  @param   string   CASトークン
     *  @param   string   KEY
     *  @param   mixed   新規追加する値
     *  @param   int      (optional) データ有効期間
     *  @return  boolean  処理結果
     */
    public /* void */
        function cas(/* string */ $cas_token,
                     /* string */ $key,
                     /* mixed  */ $value,
                     /* int    */ $expiration = NULL)
    {
        $error = '%s Driver Unsupported function :: %s';
        $this->error_code    = self::RES_UNSUPPORTED_FUNCTION;
        $this->error_message = sprintf($error, __CLASS__, __FUNCTION__);
        return FALSE;
    }
    // }}}
    // {{{ delete
    /**
     *  既存データを削除する
     *
     *  指定KEYに保存されているデータを削除する。
     *
     *  @param   string   KEY
     *  @param   int      (optional) 指定秒数待ってから削除する
     *  @return  boolean  処理結果
     */
    public /* void */
        function delete(/* string */ $key,
                        /* int    */ $time = 0)
    {
        // 変数の初期化
        $is_success  = TRUE;
        $value       = FALSE;
        $start_time  = microtime(TRUE);
        $this->error_code    = 0;
        $this->error_message = NULL;

        // ストレージKEYの生成
        $storage_key = $this->gen_storage_key($key);
        if ($storage_key === FALSE) {
            $is_success = FALSE;
        }

        // 削除処理
        if ($is_success) {
            if (apc_exists($storage_key)) {
                $is_success = apc_delete($storage_key);
                if ($is_success === FALSE) {
                    $error = 'Failed to remove stored data {namespace, key} %s %s';
                    $this->error_code    = self::RES_FAILURE;
                    $this->error_message = sprintf($error, $this->namespace, $key);
                }
            } else {
                $is_success = FALSE;
                $error = 'Not found key {namespace, key} %s %s';
                $this->error_code    = self::RES_NOTFOUND;
                $this->error_message = sprintf($error, $this->namespace, $key);
            }
        }

        // ログを記録する
        $this->operation    = __FUNCTION__;
        $this->storage_key  = $storage_key;
        $this->elapsed_time = microtime(TRUE) - $start_time;
        $this->log();

        // 結果を返す
        return $is_success;
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ increment
    /**
     *  データ値を加算処理する
     *
     *  指定KEYに保存されているデータを加算処理する。
     *   - 加算結果値を結果値として返す
     *   - データが数値型として認識できない場合は0として扱う
     *   - データが存在しない場合は、処理が失敗しFALSEを返す
     *
     *  @param   string  KEY
     *  @param   int     (optional) 加算する差分値
     *  @return  int     加算後の値
     */
    public /* void */
        function increment(/* string */ $key,
                           /* int    */ $offset = 1)
    {
        // 変数の初期化
        $is_success  = TRUE;
        $result      = FALSE;
        $start_time  = microtime(TRUE);
        $this->error_code    = 0;
        $this->error_message = NULL;

        // ストレージKEYの生成
        $storage_key = $this->gen_storage_key($key);
        if ($storage_key === FALSE) {
            $is_success = FALSE;
        }

        // 加算処理
        if ($is_success) {
            if (apc_exists($storage_key)) {
                $result = apc_inc($storage_key, $offset, $is_success);
                if ($is_success === FALSE) {
                    $error = 'Failed to increment stored value {namespace, key, offset} %s %s %d';
                    $this->error_code    = self::RES_FAILURE;
                    $this->error_message = sprintf($error, $this->namespace, $key);
                }
            } else {
                $is_success = FALSE;
                $error = 'Not found key {namespace, key} %s %s';
                $this->error_code    = self::RES_NOTFOUND;
                $this->error_message = sprintf($error, $this->namespace, $key);
            }
        }

        // ログを記録する
        $this->operation    = __FUNCTION__;
        $this->storage_key  = $storage_key;
        $this->elapsed_time = microtime(TRUE) - $start_time;
        $this->log();

        // 結果を返す
        return $result;
    }
    // }}}
    // {{{ decrement
    /**
     *  データ値を減算処理する
     *
     *  指定KEYに保存されているデータを減算処理する。
     *   - 減算結果値を結果値として返す
     *   - データが数値型として認識できない場合は0として扱う
     *   - データが存在しない場合は、処理が失敗しFALSEを返す
     *
     *  @param   string  データー・フォーマット
     *  @param   string  KEY
     *  @param   int     (optional) 減算する差分値
     *  @return  int     減算後の値
     */
    public /* void */
        function decrement(/* string */ $key,
                           /* int    */ $offset = 1)
    {
        // 変数の初期化
        $is_success  = TRUE;
        $result      = FALSE;
        $start_time  = microtime(TRUE);
        $this->error_code    = 0;
        $this->error_message = NULL;

        // ストレージKEYの生成
        $storage_key = $this->gen_storage_key($key);
        if ($storage_key === FALSE) {
            $is_success = FALSE;
        }

        // 減算処理
        if ($is_success) {
            if (apc_exists($storage_key)) {
                $result = apc_dec($storage_key, $offset, $is_success);
                if ($is_success === FALSE) {
                    $error = 'Failed to decrement stored value {namespace, key, offset} %s %s %d';
                    $this->error_code    = self::RES_FAILURE;
                    $this->error_message = sprintf($error, $this->namespace, $key);
                }
            } else {
                $is_success = FALSE;
                $error = 'Not found key {namespace, key} %s %s';
                $this->error_code    = self::RES_NOTFOUND;
                $this->error_message = sprintf($error, $this->namespace, $key);
            }
        }

        // ログを記録する
        $this->operation    = __FUNCTION__;
        $this->storage_key  = $storage_key;
        $this->elapsed_time = microtime(TRUE) - $start_time;
        $this->log();

        // 結果を返す
        return $result;
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
        // Ethna Action Name
        $action = 'none';
        if (isset($_REQUEST['mode']) && isset($_REQUEST['act'])) {
            $action = $_REQUEST['mode'].'_'.$_REQUEST['act'];
        } else if (isset($_REQUEST['mode'])) {
            $action = $_REQUEST['mode'];
        } else if (isset($_REQUEST['act'])) {
            $action = $_REQUEST['act'];
        }
        // 記録しないエラー・コード
        $ignore_error_code = array(
            self::RES_NOTFOUND,
        );
        // ログを記録
        $logger = self::get_logger();
        if (   ($this->error_code || $this->error_message)
            && (!in_array($this->error_code, $ignore_error_code))) {
            $attr_data = array(
                $action,
                get_class($this),
                '',
                number_format($this->elapsed_time, 6),
                $this->error_code,
                $this->error_message,
            );
            $logger->err(implode("\t", $attr_data));
        } else {
            $attr_data = array(
                $action,
                get_class($this),
                '',
                number_format($this->elapsed_time, 6),
                $this->operation,
                is_array($this->storage_key) ? implode(',', $this->storage_key) : $this->storage_key,
            );
            $logger->debug(implode("\t", $attr_data));
        }
    }
    // }}}
};
