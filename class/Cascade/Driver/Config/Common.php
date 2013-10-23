<?php
/**
 *  Common.php
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_Driver
 *  @subpackage  Config
 */

/**
 *  設定データ管理の抽象クラス
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_Driver
 *  @subpackage  Config
 */
abstract class Cascade_Driver_Config_Common
    extends    Cascade_Object
    implements Cascade_Driver_Config
{
    // ----[ Class Constants ]----------------------------------------
    // {{{ CONSTANT
    /**
     *  継承関係を表現するINDEX-KEY
     */
    const EXTEND_INFO_KEY     = ';parent';
    // }}}
    // ----[ Properties ]---------------------------------------------
    // {{{ CONFIG DATA
    /**
     *  設定ファイルPATH
     *  @var  string
     */
    protected $file_path      = NULL;

    /**
     *  設定ファイルのパース結果
     *  @var  array
     */
    protected $file_data      = array();

    /**
     *  設定データ構築結果
     *  @var  array
     */
    protected $processed_data = array();

    /**
     *  継承関係データ
     *  @var  string
     */
    protected $extends_data   = array();
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
     *  @param  string   設定ファイルPATH
     *  @param  string   (optional) 配列KEYを指定する場合
     */
    public /* void */
        function __construct(/* string */ $file_path,
                             /* string */ $index = NULL)
    {
        parent::__construct();

        // 設定ファイルの読み込み
        $file_data = $this->config_file_load($file_path, $index);
        // 内部データに保存
        $this->file_path = $file_path;
        $this->file_data = is_array($file_data) ? $file_data : array();
        // セクションを解析
        $this->section_process_root();
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
    // {{{ get_logger
    /**
     *  ロガーを取得する
     *
     *  @return  Cascade_Driver_Log  ロガー
     */
    public static /* Cascade_Driver_Log */
        function get_logger(/* void */)
    {
        return NULL;
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ get
    /**
     *  指定セクションの値を取得する
     *
     *  @param   string  セクション
     *  @param   mixed   (optional) 変数名
     *  @return  mixed   指定セクション格納された値
     */
    public /* mixed */
        function get(/* string */ $section,
                     /* mixed  */ $name = NULL)
    {
        // セクションが存在しない場合はNULL
        if (array_key_exists($section, $this->processed_data) === FALSE) {
            return NULL;
        }

        // 変数名が指定されていない場合
        if ($name === NULL) {
            return $this->processed_data[$section];
        }

        // 取得変数が配列値で指定されている場合
        if (is_array($name)) {
            $data = array();
            foreach ($name as $key => $var_name) {
                $data[$key] = $this->get($section, $var_name);
            }
            return $data;
        }

        // 取得変数が単一の場合
        $current =  NULL;
        $current =& $this->processed_data[$section];
        foreach (explode(Cascade::SEPARATOR_VAR_NAME_NEST, (string) $name) as $idx) {
            if (array_key_exists($idx, $current) === FALSE) {
                return NULL;
            }
            $current =& $current[$idx];
        }
        return $current;
    }
    // }}}
    // {{{ get_all
    /**
     *  設定情報を全て取得する
     *
     *  @return  array   設定情報
     */
    public /* array */
        function get_all(/* void */)
    {
        return $this->processed_data;
    }
    // }}}
    // {{{ count
    /**
     *  セクション数を取得する
     *
     *  @param   string  セクション
     *  @return  mixed   セクション数
     */
    public /* int */
        function count(/* void */)
    {
        return count($this->processed_data);
    }
    // }}}
    // ----[ Abstract Methods ]---------------------------------------
    // {{{ config_file_load
    /**
     *  PHP ARRAY ファイルの読み込み処理
     *
     *  @param   string   設定ファイルPATH
     *  @param   string   (optional) 配列KEYを指定する場合
     *  @return  array    読み込み結果データ
     */
    abstract protected /* array */
        function config_file_load(/* string */ $file_path,
                                  /* string */ $index = NULL);
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ section_process_root
    /**
     *  セクションデータを解析する (全体)
     *
     *  @return  booelan  TRUE:正常終了
     */
    protected /* boolean */
        function section_process_root(/* void */)
    {
        foreach (array_keys($this->file_data) as $section) {
            if (FALSE === $this->section_process_parts($section)) {
                return FALSE;
            }
        }
        return TRUE;
    }
    // }}}
    // {{{ section_process_parts
    /**
     *  セクションを解析する (個別)
     *
     *  @param   string   解析対象のセクション名
     *  @return  booelan  TRUE:正常終了
     */
    protected /* boolean */
        function section_process_parts(/* string */ $section)
    {
        // 指定セクションの存在確認
        if (array_key_exists($section, $this->file_data) === FALSE) {
            $error = 'Not found section data {section, file_path} %s %s';
            $this->error_code    = -1;
            $this->error_message = sprintf($error, $section, $this->file_path);
            return FALSE;
        }
        // セクションを解析する
        foreach ($this->file_data[$section] as $key => $val) {
            if ($key === self::EXTEND_INFO_KEY) {
                // 継承処理
                $child_section  = $section;
                $parent_section = $val;
                $is_success = $this->section_extends($child_section, $parent_section);
            } else {
                // 値を解析
                $is_success = $this->section_process_data($section, $key, $val);
            }
            // 解析結果を確認
            if ($is_success === FALSE) {
                return FALSE;
            }
        }
        // 正常終了
        return TRUE;
    }
    // }}}
    // {{{ section_process_data
    /**
     *  変数値を解析する
     *
     *  @param   array    解析結果データ
     *  @param   array    変数名
     *  @param   string   変数値
     *  @return  booelan  TRUE:正常終了
     */
    protected /* boolean */
        function section_process_data(/* array  */ $section,
                                      /* array  */ $key,
                                      /* string */ $val)
    {
        // データ格納位置を確定する
        $current =& $this->processed_data[$section];
        foreach (explode(Cascade::SEPARATOR_VAR_NAME_NEST, $key) as $idx) {
            $current =& $current[$idx];
        }
        // データを格納
        $current = $val;
        return TRUE;
    }
    // }}}
    // {{{ section_extends
    /**
     *  セクションを継承する
     *
     *  @param   string   子セクション
     *  @param   string   親セクション
     *  @return  boolean  TRUE:正常終了
     */
    protected /* boolean */
        function section_extends(/* string */ $child_section,
                                 /* string */ $parent_section)
    {
        // 継承関係が矛盾していなかを確認
        $current = $parent_section;
        while (array_key_exists($current, $this->extends_data)) {
            if ($this->extend_data[$current] == $child_section) {
                $error = 'Illegal circular inheritance {section file_path} %s %s';
                $this->error_code    = -1;
                $this->error_message = sprintf($error, $child_section, $this->file_path);
                return FALSE;
            }
            $current = $this->extends_data[$current];
        }
        $this->extends_data[$child_section] = $parent_section;

        // データの継承処理
        if ($this->section_process_parts($parent_section) === FALSE) {
            return FALSE;
        }
        if (isset($this->processed_data[$child_section])) {
            $this->processed_data[$child_section]
                = $this->processed_data[$child_section]
                + $this->processed_data[$parent_section];
        } else {
            $this->processed_data[$child_section]
                = $this->processed_data[$parent_section];
        }

        // 正常終了
        return TRUE;
    }
    // }}}
};