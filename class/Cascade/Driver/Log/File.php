<?php
/**
 *  File.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Driver
 */

/**
 *  ログをファイルに記録するドライバ定義
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Driver
 */
final class    Cascade_Driver_Log_File
    extends    Cascade_Driver_Log_Common
    implements Cascade_Driver_Log
{
    // ----[ Properties ]---------------------------------------------
    // {{{ FILE INFOMATION
    /**
     *  ファイル権限(8進数)
     *  @var  int
     */
    protected $file_mode   = 0666;

    /**
     *  ディレクトリ権限(8進数)
     *  @var  int
     */
    protected $dir_mode    = 0777;

    /**
     *  ファイル読み込みモード
     *  @var  string
     */
    protected $fopen_mode  = 'a';

    /**
     *  ログ出力レベル毎のディレクトリ設定
     *  @var  string
     */
    protected $default_dir = '/var/log/cascade';

    /**
     *  ログ出力レベル毎のディレクトリ設定
     *  @var  array
     */
    protected $dirs        = array(
        self::LEVEL_IS_EMERG   => NULL,
        self::LEVEL_IS_ALERT   => NULL,
        self::LEVEL_IS_CRIT    => NULL,
        self::LEVEL_IS_ERR     => NULL,
        self::LEVEL_IS_WARNING => NULL,
        self::LEVEL_IS_NOTICE  => NULL,
        self::LEVEL_IS_INFO    => NULL,
        self::LEVEL_IS_DEBUG   => NULL,
    );
    // }}}
    // ----[ Properties ]---------------------------------------------
    // {{{ RESOURCE
    /**
     *  スタックされたログデータ
     *  @var array
     */
    protected $stack           = array();

    /**
     *  ファイル・リソース
     *  @var  array
     */
    protected static $le_files = array();
    // }}}
    // ----[ Magic Methods ]------------------------------------------
    // {{{ __construct
    /**
     *  コンストラクタ
     */
    public /* void */
        function __construct(/* string */ $name,
                             /* array  */ $config = array(),
                             /* int    */ $level  = self::LEVEL_IS_INFO)
    {
        // 親クラスのコンストラクタを実行
        parent::__construct($name, $config, $level);
        // 設定情報を反映
        if (isset($config['file_mode'])) {
            $this->file_mode = $config['file_mode'];
        }
        if (isset($config['dir_mode'])) {
            $this->dir_mode = $config['dir_mode'];
        }
        if (isset($config['fopen_mode'])) {
            $this->fopen_mode = $config['fopen_mode'];
        }
        if (isset($config['default_dir'])) {
            $this->default_dir = $config['default_dir'];
        }
        if (is_array(@$config['dirs'])) {
            foreach  ($config['dirs'] as $key => $val) {
                $this->dirs[$key] = $val;
            }
        }
    }
    // }}}
    // {{{ __destruct
    /**
     *  デストラクタ
     */
    public /* void */
        function __destruct(/* void */)
    {
        $this->close_all_file_resource();
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
        return TRUE;
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
        return $version = '0.0.1';
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
        return 0;
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
        return NULL;
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
        return TRUE;
    }
    // }}}
    // {{{ get_logger
    /**
     *  ロガーを取得する
     *
     *  @retrun  Cascade_Driver_Log  ロガー
     */
    public static /* Cascade_Driver_Log */
        function get_logger(/* void */)
    {
        return NULL;
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ log
    /**
     *  ログ出力処理
     *
     *  設定レベルに応じてログを記録する
     *  (標準設定では、全てのログメッセージが記録される)
     *
     *  @param  mixed  ログ情報
     *  @param  int    出力レベル
     */
    protected /* void */
        function log(/* mixed */ $input,
                     /* int   */ $level = self::LEVEL_IS_INFO)
    {
        // 出力レベルの確認
        if ($this->isMasked($level) === FALSE) {
            return;
        }
        // ファイルリソースの取得
        $fp = $this->get_file_resource($level);
        if(is_resource($fp) === FALSE) {
            return;
        }
        // ログの書き出し
        $line = $this->format($input, $level);
        if (0 < strlen($line)) {
            fwrite($fp, $line . PHP_EOL);
        }
        // ログをスタック
        $this->stack[] = $line;
    }
    // }}}
    // {{{ get_stack
    /**
     *  スタックされたログ情報を取得する
     *
     *  @param  mixed  ログ情報
     */
    public /* void */
        function get_stack(/* void */)
    {
        return $this->stack;
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ get_file_resource
    /**
     *  ファイル・リソースを取得する
     *
     *  @param   int       出力レベル
     *  @return  resource  ファイル・リソース
     */
    protected /* resource */
        function get_file_resource(/* int */ $level)
    {
        // ログ記録ファイルを取得
        $dir = isset($this->dirs[$level])
            ? $this->dirs[$level]
            : $this->default_dir;
        $file_path = $dir . Cascade::SEPARATOR_DIRECTORY . $this->name;

        // ファイル・リソースを取得
        if (isset(self::$le_files[$file_path]) === FALSE) {
            // ディレクトリが無い場合
            if (is_dir(dirname($file_path)) === FALSE) {
                set_error_handler(array($this, 'php_error_handler'));
                $is_success = mkdir(dirname($file_path), $this->dir_mode, TRUE);
                restore_error_handler();
                if ($is_success === FALSE) {
                    $error = 'Failed to create directory {dir_path} %s';
                    $this->error_code    = -1;
                    $this->error_message = sprintf($error, dirname($file_path));
                    trigger_error($this->error_message, E_USER_NOTICE);
                    return NULL;
                }
            }
            // ファイルリソースを取得する
            $is_new  = !file_exists($file_path);
            $le_file = fopen($file_path, $this->fopen_mode);
            if (is_resource($le_file) === FALSE) {
                return NULL;
            }
            // 権限の付与
            if ($is_new) {
                chmod($file_path, $this->file_mode);
            }
            // 取得結果を返す
            self::$le_files[$file_path] = $le_file;
        }

        // リソースを返す
        return self::$le_files[$file_path];
    }
    // }}}
    // {{{ close_all_file_resource
    /**
     *  ファイル・リソースを全て解放する
     */
    protected /* void */
        function close_all_file_resource(/* void */)
    {
        foreach (self::$le_files as $key => $le_file) {
            if (is_resource($le_file)) {
                fclose($le_file);
            }
            unset(self::$le_files[$key]);
        }
    }
    // }}}
};