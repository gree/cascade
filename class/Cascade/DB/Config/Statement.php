<?php
/**
 *  Statement.php
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_DB
 *  @subpackage  Config
 */

/**
 *  Cascade_DB_Config_Statement
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_DB
 *  @subpackage  Config
 */
final class Cascade_DB_Config_Statement
    extends Cascade_DB_Statement
{
    // ----[ Class Constants ]----------------------------------------
    /** データ取得形式 : 添字配列 */
    const FETCH_MODE_NUM     = 0x01;
    /** データ取得形式 : 連想配列 */
    const FETCH_MODE_ASSOC   = 0x02;

    // ----[ Methods ]------------------------------------------------
    // {{{ prepare
    /**
     *  実行に必要な準備をする
     *
     *  ステートメント実行に必要となる準備を行う。
     *   - 実行クエリーの準備
     *   - バインド変数値の準備
     */
    public /* Cascade_DB_Statement */
        function prepare(/* void */)
    {
        // 抽出条件インスタンスを確認
        $criteria = $this->getCriteria();
        if (($criteria instanceof Cascade_DB_Config_Criteria) === FALSE) {
            $ex_msg = 'Invalid Criteria type {class} %s';
            $ex_msg = sprintf($ex_msg, get_class($criteria));
            throw new Cascade_Exception_DBException($ex_msg);
        }
        // データ・フォーマットを確認
        $data_fmt = $this->getdataFormat();
        if (($data_fmt instanceof Cascade_DB_Config_DataFormat) === FALSE) {
            $ex_msg = 'Invalid DataFormat type {class} %s';
            $ex_msg = sprintf($ex_msg, get_class($data_fmt));
            throw new Cascade_Exception_DBException($ex_msg);
        }
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
    protected /* mixed */
        function perform(/* void */)
    {
        $result   = NULL;
        $criteria = $this->getCriteria();
        $driver   = $this->getDriver();
        switch ($criteria->type) {
        case Cascade_DB_Config_Criteria::TYPE_IS_GET_ALL:
            $result = $driver->get_all($criteria);
            break;
        case Cascade_DB_Config_Criteria::TYPE_IS_GET:
            $result = $driver->get($criteria->section, $criteria->name);
            break;
        case Cascade_DB_Config_Criteria::TYPE_IS_COUNT:
            $result = $driver->count();
            break;
        default:
            $ex_msg = 'Unsupported execution type of Criteria {type} %d';
            $ex_msg = sprintf($ex_msg, $criteria->type);
            throw new Cascade_Exception_DBException($ex_msg);
        }
        return $result;
    }
    // }}}
    // {{{ getDriver
    /**
     *  ドライバーを取得する
     *
     *  データ・フォーマットの情報に該当するドライバーインスタンスを取得する。<br/>
     *  インスタンスを取得した時点でコネクションが確立される。
     *
     *  @return  Cascade_DB_Config_Driver  DB操作ドライバー
     */
    protected /* Cascade_DB_Config_Driver */
        function getDriver(/* void */)
    {
        $type = $this->getdataFormat()->getDriverType();
        $args = array(
            $this->getdataFormat()->getConfigFilePath()
        );
        $driver = Cascade_Driver_Factory::getInstance($type, $args);
        return $driver;
    }
    // }}}
};