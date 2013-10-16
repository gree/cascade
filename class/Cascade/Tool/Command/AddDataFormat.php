<?php
/**
 *  AddDataFormat.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Tool_Command
 */

/**
 *  Cascade_Tool_Command_AddDataFormat
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Tool_Command
 */
final class Cascade_Tool_Command_AddDataFormat
    extends Cascade_Tool_Command
{
    // ----[ Properties ]---------------------------------------------
    protected $config        = NULL;
    protected $command_entry = array(
        1 => 'Cascade_Tool_Command_AddDataFormat_SQL',
        2 => 'Cascade_Tool_Command_AddDataFormat_KVS',
        3 => 'Cascade_Tool_Command_AddDataFormat_Config',
    );

    // ----[ Magic Methods ]------------------------------------------
    // {{{ __construct
    /**
     *  コンストラクタ
     */
    public /* void */
        function __construct(/* void */)
    {
        parent::__construct();
        $driver = Cascade_Driver_Factory::getInstance(
            Cascade::DRIVER_INIFILE,
            CASCADE_SKEL_DIR_PATH.'/cascade-cmd.ini'
        );
        $this->config = $driver->get('add_data_format');
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
        $num = $this->get_input_value(
            $this->config['message_s_01'],
            $this->config['message_l_01'],
            $default = NULL,
            array($this, 'validate_number')
        );
        $class_name = $this->command_entry[$num];
        $command = new $class_name;
        $command->run();
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ validate_number
    /**
     *  入力値の確認処理
     *
     *  @param   mixed    入力値
     *  @return  boolean  TRUE:入力値が想定値
     */
    protected /** boolean */
        function validate_number($value)
    {
        if (isset($this->command_entry[$value]) === FALSE) {
            print PHP_EOL;
            print "ERROR :: Invalid value {number} $value".PHP_EOL.PHP_EOL;
            return FALSE;
        }
        return TRUE;
    }
    // }}}
};
