<?php
/**
 *  Statement.php
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_DB
 *  @subpackage  KVS
 */

/**
 *  Cascade_DB_KVS_Statement
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_DB
 *  @subpackage  KVS
 */
final class Cascade_DB_KVS_Statement
    extends Cascade_DB_Statement
{
    // ----[ Properties ]---------------------------------------------
    /**
     *  実行処理関数
     *  @var  ReflectionMethod
     */
    protected $method      = NULL;

    /**
     *  実行関数引数
     *  @var  array
     */
    protected $method_args = array();

    // ----[ Properties ]---------------------------------------------
    /**
     *  コマンドと関数のマッピング情報
     *  @var  array
     */
    protected static $driver_op_map = array(
        Cascade_DB_KVS_Criteria::TYPE_IS_GET   => 'get',
        Cascade_DB_KVS_Criteria::TYPE_IS_MGET  => 'mget',
        Cascade_DB_KVS_Criteria::TYPE_IS_ADD   => 'add',
        Cascade_DB_KVS_Criteria::TYPE_IS_SET   => 'set',
        Cascade_DB_KVS_Criteria::TYPE_IS_REP   => 'replace',
        Cascade_DB_KVS_Criteria::TYPE_IS_CAS   => 'cas',
        Cascade_DB_KVS_Criteria::TYPE_IS_DEL   => 'delete',
        Cascade_DB_KVS_Criteria::TYPE_IS_INC   => 'increment',
        Cascade_DB_KVS_Criteria::TYPE_IS_DEC   => 'decrement',
        Cascade_DB_KVS_Criteria::TYPE_IS_ERRNO => 'get_error_code',
        Cascade_DB_KVS_Criteria::TYPE_IS_ERROR => 'get_error_message',
    );

    // ----[ Methods ]------------------------------------------------
    // {{{ prepare
    /**
     *  ステートメントの初期化処理
     */
    public /** void */
        function prepare(/** void */)
    {
        // 内部変数の初期化
        $this->method      = NULL;
        $this->method_args = array();
        // 抽出条件インスタンスを確認
        $criteria = $this->getCriteria();
        if (($criteria instanceof Cascade_DB_KVS_Criteria) === FALSE) {
            $ex_msg = 'Invalid Criteria type {class} %s';
            $ex_msg = sprintf($ex_msg, get_class($criteria));
            throw new Cascade_Exception_DBException($ex_msg);
        }
        // データ・フォーマットを確認
        $data_fmt = $this->getDataFormat();
        if (($data_fmt instanceof Cascade_DB_KVS_DataFormat) === FALSE) {
            $ex_msg = 'Invalid DataFormat type {class} %s';
            $ex_msg = sprintf($ex_msg, get_class($data_fmt));
            throw new Cascade_Exception_DBException($ex_msg);
        }
        // 処理の実行準備
        try {
            // 関数の決定
            $this->method = self::$driver_op_map[$criteria->type];
            // 関数引数の決定
            switch ($criteria->type) {
            case Cascade_DB_KVS_Criteria::TYPE_IS_GET:
            case Cascade_DB_KVS_Criteria::TYPE_IS_MGET:
                $this->method_args[] = $criteria->key;
                break;
            case Cascade_DB_KVS_Criteria::TYPE_IS_ADD:
            case Cascade_DB_KVS_Criteria::TYPE_IS_SET:
            case Cascade_DB_KVS_Criteria::TYPE_IS_REP:
                $this->method_args[] = $criteria->key;
                $this->method_args[] = $criteria->value;
                if ($criteria->expiration !== NULL) {
                    $this->method_args[] = $criteria->expiration;
                }
                break;
            case Cascade_DB_KVS_Criteria::TYPE_IS_CAS:
                $this->method_args[] = $criteria->cas_token;
                $this->method_args[] = $criteria->key;
                $this->method_args[] = $criteria->value;
                if ($criteria->expiration !== NULL) {
                    $this->method_args[] = $criteria->expiration;
                }
                break;
            case Cascade_DB_KVS_Criteria::TYPE_IS_DEL:
                $this->method_args[] = $criteria->key;
                if ($criteria->expiration !== NULL) {
                    $this->method_args[] = $criteria->expiration;
                }
                break;
            case Cascade_DB_KVS_Criteria::TYPE_IS_INC:
            case Cascade_DB_KVS_Criteria::TYPE_IS_DEC:
                $this->method_args[] = $criteria->key;
                if ($criteria->offset !== NULL) {
                    $this->method_args[] = $criteria->offset;
                }
                break;
            case Cascade_DB_KVS_Criteria::TYPE_IS_ERRNO:
            case Cascade_DB_KVS_Criteria::TYPE_IS_ERROR:
                break;
            default:
                $ex_msg  = 'Unsupported execution type of Criteria {type} %d';
                $ex_msg  = sprintf($ex_msg, $criteria->type);
                throw new Cascade_Exception_DBException($ex_msg);
            }
        } catch (Exception $ex) {
            $this->method      = NULL;
            $this->method_args = array();
            throw $ex;
        }
        return $this;
    }
    // }}}
    // {{{ perform
    /**
     *  ステートメントを実行する
     *
     *  実行準備が整ったステートメントを実行する。
     *
     *  @return  mixed  実行結果
     */
    public /** mixed */
        function perform(/** void */)
    {
        // 実行準備が整っているか確認
        if ($this->method === NULL) {
            $ex_msg = 'Statement must be prepared {class}';
            $ex_msg = sprintf($ex_msg, get_class($this));
            throw new Cascade_Exception_DBException($ex_msg);
        }
        // 処理の実行
        $result      = TRUE;
        $criteria    = $this->getCriteria();
        $driver      = $this->getDriver();
        $driver_func = array($driver, $this->method);
        switch ($criteria->type) {
        case Cascade_DB_KVS_Criteria::TYPE_IS_GET:
        case Cascade_DB_KVS_Criteria::TYPE_IS_MGET:
            $this->method_args[] = &$cas_token;
            $value  = call_user_func_array($driver_func, $this->method_args);
            $result = array($value, $cas_token);
            break;
        case Cascade_DB_KVS_Criteria::TYPE_IS_ADD:
        case Cascade_DB_KVS_Criteria::TYPE_IS_SET:
        case Cascade_DB_KVS_Criteria::TYPE_IS_REP:
        case Cascade_DB_KVS_Criteria::TYPE_IS_CAS:
        case Cascade_DB_KVS_Criteria::TYPE_IS_DEL:
        case Cascade_DB_KVS_Criteria::TYPE_IS_INC:
        case Cascade_DB_KVS_Criteria::TYPE_IS_DEC:
        case Cascade_DB_KVS_Criteria::TYPE_IS_ERRNO:
        case Cascade_DB_KVS_Criteria::TYPE_IS_ERROR:
            $result = call_user_func_array($driver_func, $this->method_args);
            break;
        default:
            $ex_msg  = 'Unsupported execution type of Criteria {type} %d';
            $ex_msg  = sprintf($ex_msg, $criteria->type);
            throw new Cascade_Exception_DBException($ex_msg);
        }
        return $result;
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ getDriver
    /**
     *  ドライバーを取得する
     *
     *  データ・フォーマットの情報に該当するドライバーインスタンスを取得する。<br/>
     *
     *  @return  Cascade_DB_KVS_Driver  DB操作ドライバー
     */
    protected /* Cascade_DB_KVS_Driver */
        function getDriver(/* void */)
    {
        $type = $this->getDataFormat()->getDriverType();
        $args = array(
            $this->getDataFormat()->getNamespace(),
            $this->getDataFormat()->getDSN($this->getCriteria()),
            $this->getDataFormat()->isCompressed()
        );
        $driver = Cascade_Driver_Factory::getInstance($type, $args);
        return $driver;
    }
    // }}}
};