<?php
/**
 *  Factory.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Driver
 */

/**
 *  ドライバの基底抽象クラス
 *
 *  全てのドライバは、派生クラスとして定義される
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Driver
 */
abstract class Cascade_Driver_Factory
    extends    Cascade_Object
{
    // ----[ Properties ]---------------------------------------------
    /**
     *  ドライバ・クラス定義の登録
     *  @var  array
     */
    protected static $entry_map = array(
        // SQL
        Cascade::DRIVER_MYSQLI       => 'Cascade_Driver_SQL_MySQLi',
        // KVS
        Cascade::DRIVER_EAC          => 'Cascade_Driver_KVS_EAC',
        Cascade::DRIVER_APC          => 'Cascade_Driver_KVS_APC',
        Cascade::DRIVER_MEMCACHED    => 'Cascade_Driver_KVS_Memcached',
        Cascade::DRIVER_LIBMEMCACHED => 'Cascade_Driver_KVS_Libmemcached',
        Cascade::DRIVER_FLARE        => 'Cascade_Driver_KVS_Flare',
        Cascade::DRIVER_SQUALL       => 'Cascade_Driver_KVS_Squall',
        // CFG
        Cascade::DRIVER_PHPARRAY     => 'Cascade_Driver_Config_PHPArray',
        Cascade::DRIVER_INIFILE      => 'Cascade_Driver_Config_IniFile',
        Cascade::DRIVER_CSVFILE      => 'Cascade_Driver_Config_CSVFile',
        // LOG
        Cascade::DRIVER_LOG_FILE     => 'Cascade_Driver_Log_File',
    );

    // ----[ Methods ]------------------------------------------------
    // {{{ getInstance
    /**
     *  ドライバのインスタンスを取得する
     *
     *  @param   mixed                  種別, もしくはデータ定義
     *  @param   array                  (optional) コンストラクタ渡す引数を含む配列
     *  @return  Cascade_Driver_Driver  ドライバ
     */
    public final static /* Cascade_Driver_Driver */
        function getInstance(/* mixed */ $input,
                             /* array */ $args  = array())
    {
        static $instances = array();

        // ドライバ種別を決定する
        $type = ($input instanceof Cascade_DB_DataFormat)
            ? $input->getDriverType()
            : (int) $input;

        // インスタンスの取得
        $ident = md5(serialize(array($type, $args)));
        if (isset($instances[$ident]) === FALSE) {
            $instances[$ident] = self::createInstance($type, $args);
        }
        // 取得結果を返す
        return $instances[$ident];
    }
    // }}}
    // {{{ createInstance
    /**
     *  ドライバのインスタンスを作成する
     *
     *  @param   int                    種別
     *  @param   array                  (optional) コンストラクタ渡す引数を含む配列
     *  @return  Cascade_Driver_Driver  ドライバ
     */
    protected final static /* Cascade_Driver_Driver */
        function createInstance(/* mixed */ $type,
                                /* array */ $args = array())
    {
        // クラス名を取得する
        if (isset(self::$entry_map[$type]) === FALSE) {
            $ex_msg = 'Unsupported type of Driver {type} %d';
            $ex_msg = sprintf($ex_msg, $type);
            throw new Cascade_Exception_DriverException($ex_msg);
        }
        $class_name = self::$entry_map[$type];

        // ドライバーが利用可能であるか確認
        if (call_user_func(array($class_name, 'is_enable')) === FALSE) {
            $ex_msg = 'Specified driver could not be loaded {class} %s';
            $ex_msg = sprintf($ex_msg, $class_name);
            throw new Cascade_Exception_DriverException($ex_msg);
        }

        // インスタンスを生成する
        $instance = NULL;
        try {
            $ref_class = new ReflectionClass($class_name);
            $instance  = $ref_class->newInstanceArgs($args);
        } catch (Cascade_Exception_Exception $ex) {
            throw $ex;
        } catch (Exception $ex) {
            $ex_msg = 'Failed to create instance of Driver {class, error} %s %s';
            $ex_msg = sprintf($ex_msg, $class_name, $ex->getMessage());
            throw new Cascade_Exception_DriverException($ex_msg);
        }
        if ($instance->get_error_code()) {
            $ex_msg = $instance->get_error_message();
            throw new Cascade_Exception_DriverException($ex_msg);
        }
        // インスタンスを返す
        return $instance;
    }
    // }}}
};