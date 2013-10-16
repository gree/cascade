<?php
/**
 *  Facade.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Facade
 */

/**
 *  データ操作ファサードの基底となる抽象クラス
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Facade
 */
abstract class Cascade_Facade_Facade
    extends    Cascade_Object
{
    // ----[ Properties ]---------------------------------------------
    /**
     *  ファサード・クラス定義の登録
     *  @var  array
     */
    protected static $entry_map = array(
        Cascade::SESSION_SQL    => 'Cascade_Facade_SQL',
        Cascade::SESSION_KVS    => 'Cascade_Facade_KVS',
        Cascade::SESSION_CONFIG => 'Cascade_Facade_Config',
    );

    // ----[ Methods ]------------------------------------------------
    // {{{ getInstance
    /**
     *  ファサードのインスタンスを取得する
     *
     *  @param   mixed                  (optional) 種別, もしくはデータ定義
     *  @return  Cascade_Facade_Facade  ファサード
     */
    public final static /* Cascade_Facade_Facade */
        function getInstance(/* mixed */ $input = Cascade::SESSION_SQL)
    {
        static $instances = array();

        // ファサード種別を決定する
        $type = ($input instanceof Cascade_DB_DataFormat)
            ? self::getTypeFromDataFormat($input)
            : (int) $input;

        // インスタンスの取得
        if (isset($instances[$type]) === FALSE) {
            $instances[$type] = self::createInstance($type);
        }

        // 取得結果を返す
        return $instances[$type];
    }
    // }}}
    // {{{ createInstance
    /**
     *  ファサードのインスタンスを作成する
     *
     *  @param   int                    (optional) 種別
     *  @return  Cascade_Facade_Facade  ファサード
     */
    protected final static /* Cascade_Facade_Facade */
        function createInstance(/* int */ $type = Cascade::SESSION_SQL)
    {
        // クラス名を取得する
        if (isset(self::$entry_map[$type]) === FALSE) {
            $ex_msg = 'Unsupported type of Facade {type} %d';
            $ex_msg = sprintf($ex_msg, $type);
            throw new Cascade_Exception_Exception($ex_msg);
        }
        $class_name = self::$entry_map[$type];

        // インスタンスを生成する
        $instance   = new $class_name;

        // インスタンスを返す
        return $instance;
    }
    // }}}
    // {{{ getTypeFromDataFormat
    /**
     *  ファサード種別を取得する
     *
     *  @param   Cascade_DB_DataFormat  データ定義
     *  @return  int                    ファサード種別
     */
    public final static /* int */
        function getTypeFromDataFormat(Cascade_DB_DataFormat $df)
    {
        // SQLデータ定義の場合
        if ($df instanceof Cascade_DB_SQL_DataFormat) {
            return Cascade::SESSION_SQL;
        }
        // KVSデータ定義の場合
        if ($df instanceof Cascade_DB_KVS_DataFormat) {
            return Cascade::SESSION_KVS;
        }
        // 設定データ定義の場合
        if ($df instanceof Cascade_DB_Config_DataFormat) {
            return Cascade::SESSION_CONFIG;
        }
        // 想定外のデータ定義の場合
        $ex_msg = 'Unexpected Dataformat {class} %s';
        $ex_msg = sprintf($ex_msg, get_class($df));
        throw new Cascade_Exception_Exception($ex_msg);
    }
    // }}}
};