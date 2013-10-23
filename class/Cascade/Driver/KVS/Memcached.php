<?php
/**
 *  Memcached.php
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_Driver
 *  @subpackage  KVS
 */

/**
 *  Memcachedによるデータ操作処理
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_Driver
 *  @subpackage  KVS
 */
class Cascade_Driver_KVS_Memcached
    extends    Cascade_Driver_KVS_Common
    implements Cascade_Driver_KVS
{
    // ----[ Class Constants ]----------------------------------------
    // {{{ MEMCACHED RESPONSE CODE
    const RES_SUCCESS                          = Memcached::RES_SUCCESS;
    const RES_FAILURE                          = Memcached::RES_FAILURE;
    const RES_HOST_LOOKUP_FAILURE              = Memcached::RES_HOST_LOOKUP_FAILURE;
    const RES_WRITE_FAILURE                    = Memcached::RES_WRITE_FAILURE;
    const RES_UNKNOWN_READ_FAILURE             = Memcached::RES_UNKNOWN_READ_FAILURE;
    const RES_PROTOCOL_ERROR                   = Memcached::RES_PROTOCOL_ERROR;
    const RES_CLIENT_ERROR                     = Memcached::RES_CLIENT_ERROR;
    const RES_SERVER_ERROR                     = Memcached::RES_SERVER_ERROR;
    const RES_CONNECTION_SOCKET_CREATE_FAILURE = Memcached::RES_CONNECTION_SOCKET_CREATE_FAILURE;
    const RES_DATA_EXISTS                      = Memcached::RES_DATA_EXISTS;
    const RES_NOTSTORED                        = Memcached::RES_NOTSTORED;
    const RES_NOTFOUND                         = Memcached::RES_NOTFOUND;
    const RES_PARTIAL_READ                     = Memcached::RES_PARTIAL_READ;
    const RES_SOME_ERRORS                      = Memcached::RES_SOME_ERRORS;
    const RES_NO_SERVERS                       = Memcached::RES_NO_SERVERS;
    const RES_END                              = Memcached::RES_END;
    const RES_ERRNO                            = Memcached::RES_ERRNO;
    const RES_TIMEOUT                          = Memcached::RES_TIMEOUT;
    const RES_BUFFERED                         = Memcached::RES_BUFFERED;
    const RES_BAD_KEY_PROVIDED                 = Memcached::RES_BAD_KEY_PROVIDED;
    // }}}
    // ----[ Class Constants ]----------------------------------------
    // {{{ MEMCACHED BEHAVIOR OPTION
    const BEHAVIOR_NO_BLOCK                    = Memcached::OPT_NO_BLOCK;
    const BEHAVIOR_TCP_NODELAY                 = Memcached::OPT_TCP_NODELAY;
    const BEHAVIOR_HASH                        = Memcached::OPT_HASH;
    const BEHAVIOR_SOCKET_SEND_SIZE            = Memcached::OPT_SOCKET_SEND_SIZE;
    const BEHAVIOR_SOCKET_RECV_SIZE            = Memcached::OPT_SOCKET_RECV_SIZE;
    const BEHAVIOR_CACHE_LOOKUPS               = Memcached::OPT_CACHE_LOOKUPS;
    const BEHAVIOR_POLL_TIMEOUT                = Memcached::OPT_POLL_TIMEOUT;
    const BEHAVIOR_DISTRIBUTION                = Memcached::OPT_DISTRIBUTION;
    const BEHAVIOR_BUFFER_REQUESTS             = Memcached::OPT_BUFFER_WRITES;
    const BEHAVIOR_CONNECT_TIMEOUT             = Memcached::OPT_CONNECT_TIMEOUT;
    const BEHAVIOR_RETRY_TIMEOUT               = Memcached::OPT_RETRY_TIMEOUT;
    const BEHAVIOR_KETAMA_WEIGHTED             = Memcached::OPT_LIBKETAMA_COMPATIBLE;
    const BEHAVIOR_BINARY_PROTOCOL             = Memcached::OPT_BINARY_PROTOCOL;
    const BEHAVIOR_SND_TIMEOUT                 = Memcached::OPT_SEND_TIMEOUT;
    const BEHAVIOR_RCV_TIMEOUT                 = Memcached::OPT_RECV_TIMEOUT;
    const BEHAVIOR_SERVER_FAILURE_LIMIT        = Memcached::OPT_SERVER_FAILURE_LIMIT;
    // }}}
    // ----[ Class Constants ]----------------------------------------
    // {{{ COMPRESS OPTION
    const OPT_COMPRESSION                      = Memcached::OPT_COMPRESSION;
    const OPT_PREFIX_KEY                       = Memcached::OPT_PREFIX_KEY;
    const OPT_SERIALIZER                       = Memcached::OPT_SERIALIZER;
    // }}}
    // ----[ Properties ]---------------------------------------------
    // {{{ SERVER
    /**
     *  名前空間
     *  @var  string
     */
    protected $namespace       = NULL;

    /**
     *  パース処理されたDSN情報
     *  @var string
     */
    protected $dsn             = NULL;

    /**
     *  圧縮フラグ
     *  @var  boolean
     */
    protected $compressed      = NULL;
    // }}}
    // ----[ Properties ]---------------------------------------------
    // {{{ OPERATION
    /**
     *  格納KEY
     *  @var  string
     */
    protected $storage_key    = NULL;

    /**
     *  処理種別
     *  @var  string
     */
    protected $operation      = NULL;

    /**
     *  実行時間
     *  @var  int
     */
    protected $elapsed_time   = 0;
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
     *  Memcachedのクライアント・インスタンスの初期化処理、<br />
     *  接続対象サーバのリスト登録を行う。
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
        $this->namespace  = $namespace;
        $this->compressed = $compressed;
        $this->dsn        = Cascade_System_DSN::parse($dsn);
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
        $extension_name   = 'memcached';
        $required_version = '1.0.2';

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
        $extension_name = 'memcached';
        return phpversion($extension_name);
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ get_client
    /**
     *  サーバリストを取得
     *
     *  クライアントに登録するサーバリストを取得する。
     *
     *  @return  array  サーバーリスト
     */
    protected /* void */
        function get_client(/* void */)
    {
        static $instances = array();

        // クライアントを取得
        $token = $this->dsn['string'].date('Ymd_H');
        if (isset($instances[$token]) === FALSE) {
            $client = new Memcached($token);
            $client->setOption(self::BEHAVIOR_SERVER_FAILURE_LIMIT, 2);
            $extension_version = phpversion('memcached');
            if (version_compare($extension_version, '2.0.1', '>=')) {
                $client->setOption(Memcached::OPT_TCP_KEEPALIVE, 1);
            }
            if ($this->compressed) {
                $client->setOption(self::OPT_COMPRESSION, 1);
            }
            $instances[$token] = $client;
        }
        $client = $instances[$token];

        // サーバ登録処理
        if (count($client->getServerList()) < 1) {
            $server_list = array();
            foreach ($this->dsn['extra']['hostspec'] as $hostspec) {
                list($host, $port) = (strpos($hostspec, ':') !== FALSE)
                    ? explode(':', $hostspec)
                    : array($hostspec, NULL);
                $server_list[] = array($host, $port);
            }
            $client->addServers($server_list);
        }

        // サーバリストが登録されたか確認する
        if (count($client->getServerList()) < 1) {
            $error = 'Not found server list {dsn} %s';
            $this->error_code    = self::RES_BAD_KEY_PROVIDED;
            $this->error_message = sprintf($error, $this->dsn['string']);
            return FALSE;
        }

        // クライアントを返す
        return $client;
    }
    // }}}
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

        // クライアントと、ストレージKEYの生成
        $client      = $this->get_client();
        $storage_key = $this->gen_storage_key($key);
        if ($client === FALSE || $storage_key === FALSE) {
            $is_success = FALSE;
        }

        // 格納データを取得
        if ($is_success) {
            $value = $client->get($storage_key, NULL, $cas_token);
            if ($value === FALSE) {
                $is_success  = FALSE;
                $this->error_code    = $client->getResultCode();
                $this->error_message = $client->getResultMessage();
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

        // クライアントと、ストレージKEYの生成
        $client       = $this->get_client();
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
            $tmp_1 = $client->getMulti($storage_keys, $tmp_2);
            if ($tmp_1) {
                $storage_key_flip = array_flip($storage_keys);
                foreach ($tmp_1 as $key => $val) {
                    $values[$storage_key_flip[$key]] = $val;
                }
                foreach ($tmp_2 as $key => $val) {
                    $cas_tokens[$storage_key_flip[$key]] = $val;
                }
            } else {
                $is_success  = FALSE;
                $this->error_code    = $client->getResultCode();
                $this->error_message = $client->getResultMessage();
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

        // クライアントと、ストレージKEYの生成
        $client      = $this->get_client();
        $storage_key = $this->gen_storage_key($key);
        if ($client === FALSE || $storage_key === FALSE) {
            $is_success = FALSE;
        }

        // 追加処理
        if ($is_success) {
            $is_success = $client->add($storage_key, $value, $expiration);
            if ($is_success === FALSE) {
                $this->error_code    = $client->getResultCode();
                $this->error_message = $client->getResultMessage();
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

        // クライアントと、ストレージKEYの生成
        $client      = $this->get_client();
        $storage_key = $this->gen_storage_key($key);
        if ($client === FALSE || $storage_key === FALSE) {
            $is_success = FALSE;
        }

        // 更新処理
        if ($is_success) {
            $is_success = $client->set($storage_key, $value, $expiration);
            if ($is_success === FALSE) {
                $this->error_code    = $client->getResultCode();
                $this->error_message = $client->getResultMessage();
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

        // クライアントと、ストレージKEYの生成
        $client      = $this->get_client();
        $storage_key = $this->gen_storage_key($key);
        if ($client === FALSE || $storage_key === FALSE) {
            $is_success = FALSE;
        }

        // 更新処理
        if ($is_success) {
            $is_success = $client->replace($storage_key, $value, $expiration);
            if ($is_success === FALSE) {
                $this->error_code    = $client->getResultCode();
                $this->error_message = $client->getResultMessage();
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
        // 変数の初期化
        $is_success  = TRUE;
        $start_time  = microtime(TRUE);
        $this->error_code    = 0;
        $this->error_message = NULL;

        // クライアントと、ストレージKEYの生成
        $client      = $this->get_client();
        $storage_key = $this->gen_storage_key($key);
        if ($client === FALSE || $storage_key === FALSE) {
            $is_success = FALSE;
        }

        // CASトークンの確認
        if ((int) $cas_token < 1) {
            $error = 'Invalid a cas_token {namespace, storage_key, cas_token} %s %s';
            $this->error_code    = self::RES_DATA_EXISTS;
            $this->error_message = sprintf($error, $this->namespace, $storage_key, $cas_token);
            $is_success = FALSE;
        }

        // 更新処理
        if ($is_success) {
            $is_success = $client->cas((int)$cas_token, $storage_key, $value, $expiration);
            if ($is_success === FALSE) {
                $this->error_code    = $client->getResultCode();
                $this->error_message = $client->getResultMessage();
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

        // クライアントと、ストレージKEYの生成
        $client      = $this->get_client();
        $storage_key = $this->gen_storage_key($key);
        if ($client === FALSE || $storage_key === FALSE) {
            $is_success = FALSE;
        }

        // 削除処理
        if ($is_success) {
            $is_success = $client->delete($storage_key, $time);
            if ($is_success === FALSE) {
                $this->error_code    = $client->getResultCode();
                $this->error_message = $client->getResultMessage();
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

        // クライアントと、ストレージKEYの生成
        $client      = $this->get_client();
        $storage_key = $this->gen_storage_key($key);
        if ($client === FALSE || $storage_key === FALSE) {
            $is_success = FALSE;
        }

        // 加算処理
        if ($is_success) {
            $result = $client->increment($storage_key, $offset);
            if ($result === FALSE) {
                $is_success  = FALSE;
                $this->error_code    = $client->getResultCode();
                $this->error_message = $client->getResultMessage();
            }
        }

        // ログを記録する
        $this->operation    = __FUNCTION__;
        $this->storage_key  = $storage_key;
        $this->elapsed_time = microtime(TRUE) - $start_time;
        $this->log();

        // 結果を返す
        return ($is_success) ? $result : FALSE;
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

        // クライアントと、ストレージKEYの生成
        $client      = $this->get_client();
        $storage_key = $this->gen_storage_key($key);
        if ($client === FALSE || $storage_key === FALSE) {
            $is_success = FALSE;
        }

        // 減算処理
        if ($is_success) {
            $result = $client->decrement($storage_key, $offset);
            if ($result === FALSE) {
                $is_success  = FALSE;
                $this->error_code    = $client->getResultCode();
                $this->error_message = $client->getResultMessage();
            }
        }

        // ログを記録する
        $this->operation    = __FUNCTION__;
        $this->storage_key  = $storage_key;
        $this->elapsed_time = microtime(TRUE) - $start_time;
        $this->log();

        // 結果を返す
        return ($is_success) ? $result : FALSE;
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
            self::RES_BUFFERED,
            self::RES_END,
            self::RES_NOTSTORED,
            self::RES_NOTFOUND,
        );
        // ログを記録
        $logger = self::get_logger();
        if (   ($this->error_code || $this->error_message)
            && (!in_array($this->error_code, $ignore_error_code))) {
            $attr_data = array(
                $action,
                get_class($this),
                $this->dsn['string'],
                number_format($this->elapsed_time, 6),
                $this->error_code,
                $this->error_message,
            );
            $logger->err(implode("\t", $attr_data));
        } else {
            $attr_data = array(
                $action,
                get_class($this),
                $this->dsn['string'],
                number_format($this->elapsed_time, 6),
                $this->operation,
                is_array($this->storage_key) ? implode(',', $this->storage_key) : $this->storage_key,
            );
            $logger->debug(implode("\t", $attr_data));
        }
    }
    // }}}
};
