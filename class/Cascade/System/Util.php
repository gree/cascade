<?php
/**
 *  Util.php
 *
 *  @author   Hiroki Uemura <hiroki.uemura@gree.co.jp>
 *  @package  Cascade_System
 */

/**
 *  Cascade_System_Util
 *
 *  @author   Hiroki Uemura <hiroki.uemura@gree.co.jp>
 *  @package  Cascade_System
 */
final class Cascade_System_Util
{
    // ----[ Methods ]------------------------------------------------
    // {{{ export
    /**
     *  var_export の代替
     *
     *  @param   mixed   エクスポートしたい変数
     *  @param   bool    (optional) TRUE に設定された場合、出力する代わりに返します
     *  @return  string  設定情報値
     */
    public static /* string */
        function export(/* string */ $expression,
                     /* bool */ $return = FALSE)
    {
        $dump = self::dump($expression);
        if ($return) {
            return $dump;
        }
        print $dump;
    }
    // }}}

    // {{{ dump
    /**
     * 変数を文字列表現にダンプする
     *
     * @param  mixed  ダンプしたい変数
     * @param  int    (optional) ダンプする最大の深さ
     * @param  int    (optional) ダンプする最小の深さ
     */
    public static /* string */
        function dump($var, $max_depth = 5, $depth = 0)
    {
        if (is_object($var) || is_array($var)) {
            $class_name;
            if (is_object($var)) {
                $class_name = get_class($var);
                $var = (array) $var;
            } else {
                $class_name = "array";
            }
            $s = "$class_name(\n";
            if (++$depth > $max_depth) {
                $s .= str_repeat('    ', $depth) . "?";
            } else {
                $flatten = array();
                foreach ($var as $k => $v) {
                    $flatten[] = str_repeat('    ', $depth) . "$k: " . self::dump($v, $max_depth, $depth);
                }
                $s .= implode(",\n", $flatten);
            }
            $s .= "\n" . str_repeat('    ', $depth - 1) . ")";
            return $s;
        }
        return "$var";
    }
    // }}} 
};
