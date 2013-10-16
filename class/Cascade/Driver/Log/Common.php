<?php
/**
 *  Common.php
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_Driver
 *  @subpackage  Log
 */

/**
 *  ログを扱うドライバー抽象定義
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_Driver
 *  @subpackage  Log
 */
abstract class Cascade_Driver_Log_Common
    implements Cascade_Driver_Log
{
    // ----[ Class Constants ]----------------------------------------
    /**
     *  全てのエラー情報を出力する設定
     */
    const OUTPUT_MASK_ALL  = 0xFF;

    /**
     *  全てのエラー情報を出力しない設定
     */
    const OUTPUT_MASK_NONE = 0x00;

    // ----[ Properties ]---------------------------------------------
    /**
     *  ログ名
     *  @var  string
     */
    protected $name        = NULL;

    /**
     *  ログ出力レベル
     *  @var  int
     */
    protected $mask        = self::OUTPUT_MASK_ALL;

    /**
     *  ログ出力フォーマット
     *  @var  string
     */
    protected $format_line = '%1$s [%2$s] %3$s';

    /**
     *  ログ出力フォーマット(日付のフォーマット)
     *  @var  string
     */
    protected $format_time = '%Y-%m-%d %H:%M:%S';

    /**
     *  バックトレース情報を含める場合はTRUEを指定
     *  @var  boolean
     */
    protected $add_trace   = FALSE;

    // ----[ Properties ]---------------------------------------------
    /**
     *  ログ出力レベルと、出力後のメッセージの対応付け
     *  @var  array
     */
    protected static $level_to_str  = array(
        self::LEVEL_IS_EMERG   => 'EMERGENCY',
        self::LEVEL_IS_ALERT   => 'ALERT',
        self::LEVEL_IS_CRIT    => 'CRITICAL',
        self::LEVEL_IS_ERR     => 'ERROR',
        self::LEVEL_IS_WARNING => 'WARNING',
        self::LEVEL_IS_NOTICE  => 'NOTICE',
        self::LEVEL_IS_INFO    => 'INFO',
        self::LEVEL_IS_DEBUG   => 'DEBUG',
    );

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
        // 設定情報を反映
        if (isset($config['format_line'])) {
            $this->format_line = $config['format_line'];
        }
        if (isset($config['format_time'])) {
            $this->format_time = $config['format_time'];
        }
        if (isset($config['add_trace'])) {
            $this->add_trace   = $config['add_trace'];
        }
        $this->name = $name;
        $this->mask = $this->toMaxMask($level);
    }
    // }}}

    // ----[ Abstraction Methods ]------------------------------------
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
    abstract protected /* void */
        function log(/* mixed */ $input,
                     /* int   */ $level = self::LEVEL_IS_INFO);
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ emerg
    /**
     *  ログ出力処理 :: EMERGENCY
     *
     *  @param  mixed  ログ情報
     */
    public final /* void */
        function emerg(/* mixed */ $input)
    {
        $this->log($input, self::LEVEL_IS_EMERG);
    }
    // }}}
    // {{{ alert
    /**
     *  ログ出力処理 :: ALERT
     *
     *  @param  mixed  ログ情報
     */
    public final /* void */
        function alert(/* mixed */ $input)
    {
        $this->log($input, self::LEVEL_IS_ALERT);
    }
    // }}}
    // {{{ crit
    /**
     *  ログ出力処理 :: CRITICAL
     *
     *  @param  mixed  ログ情報
     */
    public final /* void */
        function crit(/* mixed */ $input)
    {
        $this->log($input, self::LEVEL_IS_CRIT);
    }
    // }}}
    // {{{ err
    /**
     *  ログ出力処理 :: ERROR
     *
     *  @param  mixed  ログ情報
     */
    public final /* void */
        function err(/* mixed */ $input)
    {
        $this->log($input, self::LEVEL_IS_ERR);
    }
    // }}}
    // {{{ warn
    /**
     *  ログ出力処理 :: WARNING
     *
     *  @param  mixed  ログ情報
     */
    public final /* void */
        function warn(/* mixed */ $input)
    {
        $this->log($input, self::LEVEL_IS_WARNING);
    }
    // }}}
    // {{{ notice
    /**
     *  ログ出力処理 :: NOTICE
     *
     *  @param  mixed  ログ情報
     */
    public final /* void */
        function notice(/* mixed */ $input)
    {
        $this->log($input, self::LEVEL_IS_NOTICE);
    }
    // }}}
    // {{{ info
    /**
     *  ログ出力処理 :: INFOMATION
     *
     *  @param  mixed  ログ情報
     */
    public final /* void */
        function info(/* mixed */ $input)
    {
        $this->log($input, self::LEVEL_IS_INFO);
    }
    // }}}
    // {{{ debug
    /**
     *  ログ出力処理 :: DEBUG
     *
     *  @param  mixed  ログ情報
     */
    public final /* void */
        function debug(/* mixed */ $input)
    {
        $this->log($input, self::LEVEL_IS_DEBUG);
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ isMasked
    /**
     *  出力レベルの対象に含まれているかを確認する
     *
     *  @param   int      出力レベル
     *  @return  boolean  出力対象の場合TRUEを返す
     */
    protected final /* boolean */
        function isMasked(/* int */ $level)
    {
        return ($this->toMask($level) & $this->mask) ? TRUE : FALSE;
    }
    // }}}
    // {{{ toMaxMask
    /**
     *  指定出力レベルを最大にするビットマスクを取得する
     *
     *  @param   int  出力レベル
     *  @return  int  ビットマスク
     */
    protected final /* int */
        function toMaxMask(/* int */ $level)
    {
        return ((1 << ($level + 1)) - 1);
    }
    // }}}
    // {{{ toMinMask
    /**
     *  指定出力レベルを最小にするビットマスクを取得する
     *
     *  @param   int  出力レベル
     *  @return  int  ビットマスク
     */
    protected final /* int */
        function toMinMask(/* int */ $level)
    {
        return self::OUTPUT_MASK_ALL ^ ((1 << $level) - 1);
    }
    // }}}
    // {{{ toMask
    /**
     *  指定出力レベルを出力するビットマスクを取得する
     *
     *  @param   int  出力レベル
     *  @return  int  ビットマスク
     */
    protected final /* int */
        function toMask(/* int */ $level)
    {
        return (1 << $level);
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ format
    /**
     *  ログ記録するメッセージを構築する
     *
     *  ログ情報と指定された出力形式をもとに記録する文字列を生成する
     *
     *  @param   mixed       ログ情報
     *  @param   int         出力レベル
     *  @retrun  string      ログメッセージ
     */
    protected final /* string */
        function format(/* mixed */ $input,
                        /* int   */ $level)
    {
        // ログ情報を文字列に展開
        $out_message = $this->extract_message($input);
        if ($this->add_trace !== FALSE) {
            if (strlen($out_message) !== 0) {
                $out_message .= "\n";
            }
            $out_message .= $this->get_backtrace_as_string($input);
        }
        // 指定形式に整形する
        $out_message = sprintf(
            $this->format_line,
            strftime($this->format_time),
            self::$level_to_str[$level],
            $out_message
        );
        // ログメッセージを返す
        return $out_message;
    }
    // }}}
    // {{{ extract_message
    /**
     *  ログ情報を文字列に展開する
     *
     *  @param   mixed   ログ情報
     *  @retrun  string  ログメッセージ
     */
    protected final /* string */
        function extract_message(/* mixed */ $input)
    {
        $output_str = '';
        switch (gettype($input)) {
        case 'array':
            $output_str = Cascade_System_Util::export($input, TRUE);
            break;
        case 'object':
            if (method_exists($input, 'getMessage')) {
                $output_str = $input->getMessage();
            } else if (method_exists($input, 'toString')) {
                $output_str = $input->toString();
            } else if (method_exists($input, '__tostring')) {
                $output_str = (string)$input;
            } else {
                $output_str = Cascade_System_Util::export($input, TRUE);
            }
            break;
        default:
            $output_str = (string) $input;
            break;
        }
        return $output_str;
    }
    // }}}
    // {{{ get_backtrace_as_string
    /**
     *  バックトレース情報を文字列で取得する
     *
     *  @param   $input   ログ情報
     *  @return  string   バックトレース情報
     */
    protected final /* string */
        function get_backtrace_as_string(/* mixed */ $input)
    {
        // バックトレース情報を取得
        $bt = NULL;
        if (is_object($input)) {
            if (method_exists($input, 'getTrace')) {
                $bt = $input->getTrace();
            } else if (method_exists($input, 'getBackTrace')) {
                $bt = $input->getBackTrace();
            }
        }
        if ($bt === NULL) {
            $bt = debug_backtrace();
        }
        // 文字列に整形する
        $bt_str = '';
        foreach ($bt as $frame => $var) {
            // get trace infomation
            $class = isset($var['class']) ? $var['class'] : '';
            $func  = isset($var['function'])  ? $var['function'] : '';
            $file  = isset($var['file'])  ? $var['file'] : 'built-in function';
            $line  = isset($var['line'])  ? $var['line'] : '-';
            // build string
            $text  = '  #%-2d %s(%d) %s::%s';
            $text  = sprintf($text, $frame, $file, $line, $class, $func);
            if (strlen($bt_str) !== 0) {
                $bt_str .= "\n";
            }
            $bt_str .= $text;
        }
        return $bt_str;
    }
    // }}}
};
