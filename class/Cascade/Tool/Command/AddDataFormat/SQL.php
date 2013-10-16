<?php
/**
 *  SQL.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Tool_Command
 */

/**
 *  Cascade_Tool_Command_AddDataFormat_SQL
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Tool_Command
 */
final class Cascade_Tool_Command_AddDataFormat_SQL
    extends Cascade_Tool_Command
{
    // ----[ Properties ]---------------------------------------------
    /** スキーマ:名前空間   */
    protected $namespace    = NULL;
    /** スキーマ:識別子     */
    protected $identifier   = NULL;
    /** DSN識別子           */
    protected $dsn_ident    = NULL;
    /** テーブル名          */
    protected $table_name   = NULL;

    /** テーブル:ステータス */
    protected $table_status = NULL;
    /** テーブル:フィールド */
    protected $table_fields = NULL;

    /** 設定情報            */
    protected $config       = NULL;
    /** ユーザ設定          */
    protected $default      = NULL;

    // ----[ Magic Methods ]------------------------------------------
    // {{{ __construct
    /**
     *  コンストラクタ
     */
    public /* void */
        function __construct(/* void */)
    {
        parent::__construct();
        // 設定情報を取得
        $driver = Cascade_Driver_Factory::getInstance(
            Cascade::DRIVER_INIFILE,
            CASCADE_SKEL_DIR_PATH.'/cascade-cmd.ini'
        );
        $this->config = $driver->get('add_data_format_sql');
        // ユーザ設定情報を取得
        if (file_exists($_ENV['HOME'].'/.cascade.ini')) {
            $driver = Cascade_Driver_Factory::getInstance(
                Cascade::DRIVER_INIFILE,
                $_ENV['HOME'].'/.cascade.ini'
            );
        }
        $this->default = $driver->get('command');
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ run
    /**
     *  コマンドの実行処理
     */
    public /* void */
        function run(/* void */)
    {
        // スキーマ(名前空間)
        $default = isset($this->default['schema_namespace'])
            ? $this->default['schema_namespace']
            : NULL;
        $this->schema_ns = $this->get_input_value(
            $this->config['message_s_01'],
            $this->config['message_l_01'],
            $default,
            array($this, 'validate_schema_ns')
        );
        // スキーマ(識別子)
        $this->schema_ident = $this->get_input_value(
            $this->config['message_s_02'],
            $this->config['message_l_02'],
            $default = NULL,
            array($this, 'validate_schema_ident')
        );
        // スキーマ名の構築
        $this->schema_name = $this->schema_ns
            .Cascade_Controller::SCHEMA_SEPARATOR
            .$this->schema_ident;
        // DSN (識別子)
        $this->dsn_ident = $this->get_input_value(
            $this->config['message_s_03'],
            $this->config['message_l_03'],
            $default = NULL,
            array($this, 'validate_dsn_ident')
        );
        // テーブル名
        $this->table_name = $this->get_input_value(
            $this->config['message_s_04'],
            $this->config['message_l_04'],
            $default = NULL,
            array($this, 'validate_table_name')
        );
        $this->generate();
    }
    // }}}
    // {{{ generate
    /**
     *  データーフォーマットの生成
     */
    protected /* void */
        function generate(/* void */)
    {
        // データーフォーマット情報を取得
        $class_name = Cascade_Controller::getDataFormatClassName($this->schema_name);
        $file_path  = Cascade_Controller::getClassFilePath($this->schema_name, $class_name);
        if (file_exists($file_path) !== FALSE) {
            $ex_msg = 'Already exists a file of DataFormat {filename} %s';
            $ex_msg = sprintf($ex_msg, $file_path);
            throw new Cascade_Exception_Exception($ex_msg);
        }
        // Smarty初期化
        include_once('Smarty/Smarty.class.php');
        $smarty = new Smarty;
        $smarty->template_dir = CASCADE_SKEL_DIR_PATH;
        $smarty->compile_dir  = '/var/tmp';
        // 変数値の埋め込み
        $smarty->assign('class_name', $class_name);
        $smarty->assign('table_name', $this->table_name);
        $smarty->assign('dsn_ident',  $this->dsn_ident);
        if ($this->table_status !== NULL) {
            $auto_increment = ($this->table_status['Auto_increment'] !== NULL) ? 'TRUE' : 'FALSE';
            $smarty->assign('auto_increment', $auto_increment);
        }
        if ($this->table_fields !== NULL) {
            $field_names = array();
            foreach ($this->table_fields as $tmp) {
                $field_names[] = $tmp['Field'];
            }
            $smarty->assign('field_names', $field_names);
        }
        if (isset($this->default['author'])) {
            $smarty->assign('author',  $this->default['author']);
        }
        if (isset($this->default['package'])) {
            $smarty->assign('package', $this->default['package']);
        }
        // 生成
        $tmp = $smarty->fetch('data-format-sql.php.tpl');
        $smarty->clear_compiled_tpl();
        if (FALSE === file_put_contents($file_path, $tmp, LOCK_EX)) {
            throw new Cascade_Exception_Exception('Could not generate a file');
        }
        // 完了
        $message  = $this->config['message_done'].PHP_EOL;
        $message .= '    class_name :: '.$class_name.PHP_EOL;
        $message .= '    file_path  :: '.$file_path;
        $this->print_long_message($message);
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ validate_schema_ns
    /**
     *  入力値の確認処理
     *
     *  @param   mixed    入力値
     *  @return  boolean  TRUE:入力値が想定値
     */
    protected /* boolean */
        function validate_schema_ns($value)
    {
        try {
            if (NULL === ($schema = Cascade_System_Config::getSchema($value))) {
                $ex_msg = 'Undefined namespace of schema {namespace} %s';
                $ex_msg = sprintf($ex_msg, $value);
                throw new Cascade_Exception_Exception($ex_msg);
            }
        } catch (Exception $ex) {
            print sprintf('ERROR :: %s', $ex->getMessage()).PHP_EOL.PHP_EOL;
            return FALSE;
        }
        return TRUE;
    }
    // }}}
    // {{{ validate_schema_ident
    /**
     *  入力値の確認処理
     *
     *  @param   mixed    入力値
     *  @return  boolean  TRUE:入力値が想定値
     */
    protected /* boolean */
        function validate_schema_ident($value)
    {
        return (strlen($value) < 1) ? FALSE : TRUE;
    }
    // }}}
    // {{{ validate_dsn_ident
    /**
     *  入力値の確認処理
     *
     *  @param   mixed    入力値
     *  @return  boolean  TRUE:入力値が想定値
     */
    protected /* boolean */
        function validate_dsn_ident($value)
    {
        // DSNのエントリーを調べる
        $dsn = sprintf('gree://master/%s', $value);
        try {
            set_error_handler(array($this, 'err_dsn_parse'));
            Cascade_System_DSN::parse($dsn);
            restore_error_handler();
        } catch (Exception $ex) {
            print sprintf('WARNING :: %s', $ex->getMessage()).PHP_EOL.PHP_EOL;
            $line = $this->get_input_value(
                $this->config['notice_01'],
                $l_message = NULL,
                $default   = 'no'
            );
            return ($line === 'yes') ? TRUE : FALSE;
        }
        return TRUE;
    }
    // }}}
    // {{{ validate_table_name
    /**
     *  入力値の確認処理
     *
     *  @param   mixed    入力値
     *  @return  boolean  TRUE:入力値が想定値
     */
    protected /* boolean */
        function validate_table_name($value)
    {
        // 空白文字列は許可しない
        if (strlen($value) < 1) {
            return FALSE;
        }
        // ドライバー取得
        $driver = NULL;
        set_error_handler(array($this, 'err_dsn_parse'));
        try {
            $driver = Cascade_Driver_Factory::getInstance(
                Cascade::DRIVER_MYSQLI,
                sprintf('gree://master/%s', $this->dsn_ident)
             );
        } catch (Exception $ex) {
        }
        restore_error_handler();
        // テーブルの存在を確認する
        if ($driver !== NULL) {
            $query = sprintf("SHOW TABLE STATUS LIKE '%s'", $value);
            $driver->query($query);
            $status = $driver->fetch_all();
            if (count($status) < 1) {
                // 処理を続けるか問い合わせる
                print sprintf('WARNING :: Table does not exist {table_name} %s', $value).PHP_EOL.PHP_EOL;
                $line = $this->get_input_value(
                    $this->config['notice_02'],
                    $l_message = NULL,
                    $default   = 'no'
                );
                return ($line === 'yes') ? TRUE : FALSE;
            }
            $query  = sprintf('SHOW FIELDS FROM %s', $value);
            $driver->query($query);
            $fields = $driver->fetch_all();
            $this->table_status = array_shift($status);
            $this->table_fields = $fields;
        }
        return TRUE;
    }
    // }}}
    // {{{ err_dsn_parse
    /**
     *  エラーハンドリング関数
     */
    public /* void */
        function err_dsn_parse(/* int    */ $errno,
                               /* string */ $error,
                               /* string */ $filename,
                               /* int    */ $lineno)
    {}
    // }}}
};
