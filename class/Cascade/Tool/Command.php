<?php
/**
 *  Command.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Tool
 */

/**
 *  Cascade_Tool_Command
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Tool
 */
abstract class Cascade_Tool_Command
    extends    Cascade_Object
{
    // ----[ Class Constants ]----------------------------------------
    const TO_ENCODING   = 'EUC-JP';
    const FROM_ENCODING = 'UTF-8';

    // ----[ Abstraction Methods ]------------------------------------
    /**
     *  コマンドの実行処理
     */
    abstract public /* void */
        function run(/* void */);

    // ----[ Methods ]------------------------------------------------
    // {{{ get_input_value
    /**
     *  入力値を取得する
     *
     *  @param   string  メッセージ (short)
     *  @param   string  メッセージ (long)
     *  @param   mixed   (optional) 入力デフォルト値
     *  @return  mixed   入力値
     */
    protected final /* mixed */
        function get_input_value(/* string   */ $s_message,
                                 /* string   */ $l_message,
                                 /* mixed    */ $default = NULL,
                                 /* callback */ $cb_func = NULL)
    {
        if (0 < strlen($l_message)) {
            $this->print_long_message($l_message);
        }
        for (;;) {
            // 入力メッセージ
            $tmp = ($default === NULL)
                ? sprintf('%s > ',     $s_message)
                : sprintf('%s[%s] > ', $s_message, $default);
            $this->print_short_message($tmp);
            // 入力値の読み込み
            $stdin = @fopen("php://stdin", "r");
            if (FALSE === $stdin) {
                throw new Exception('Could not open a handle of stdin');
            }
            $value = trim(fgets($stdin, 64));
            fclose($stdin);
            // デフォルト値の適応
            if (strlen($value) < 1) {
                $value = $default;
            }
            // 入力値を確認
            if ($cb_func !== NULL && is_callable($cb_func)) {
                if (call_user_func($cb_func, $value) === FALSE) {
                    continue;
                }
            }
            break;
        }
        return $value;
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ print_long_message
    /**
     *  ロング・メッセージを出力する
     *
     *  @param  string  メッセージ
     */
    protected final /** void */
        function print_long_message(/* string */ $message)
    {
        if (NULL !== ($message = $this->convert_encoding($message))) {
            $lines = explode(PHP_EOL, $message);
            $width = $this->get_max_width($lines);
            print '+-'.str_repeat('-', $width).'-+'.PHP_EOL;
            foreach ($lines as $token) {
                print '| '.str_pad($token, $width, ' ').' |'.PHP_EOL;
            }
            print '+-'.str_repeat('-', $width).'-+'.PHP_EOL;
        }
    }
    // }}}
    // {{{ print_short_message
    /**
     *  ロング・メッセージを出力する
     *
     *  @param  string  メッセージ
     */
    protected final /** void */
        function print_short_message(/* string */ $message)
    {
        if (NULL !== ($message = $this->convert_encoding($message))) {
            $lines = explode(PHP_EOL, $message);
            print $message;
        }
    }
    // }}}
    // {{{ convert_encoding
    /**
     *  文字列をコンソール出力用にエンコーディングする
     *
     *  @param   string  メッセージ
     *  @return  string  文字エンコード変換後のメッセージ
     */
    protected final /* string */
        function convert_encoding(/* string */ $message)
    {
        if (strlen($message) < 0) {
            return NULL;
        }
        $message = mb_convert_encoding($message, self::TO_ENCODING, self::FROM_ENCODING);
        return $message;
    }
    // }}}
    // {{{ get_max_width
    /**
     *  複数行文字列の最大幅を取得する
     *
     *  @param   array  文字列
     *  @return  int    最大幅
     */
    protected final /* int */
        function get_max_width(/* array */ $lines)
    {
        $width = 0;
        foreach ($lines as $token) {
            if ($width < strlen($token)) {
                $width = strlen($token);
            }
        }
        return $width;
    }
    // }}}
};