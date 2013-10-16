<?php
/**
 *  ShardSelector.php
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_DB
 *  @subpackage  SQL
 */

/**
 *  Cascade_DB_SQL_ShardSelector
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_DB
 *  @subpackage  SQL
 */
abstract class Cascade_DB_SQL_ShardSelector
    extends    Cascade_Object
    implements Cascade_DB_ShardSelector
{
    // ----[ Properties ]---------------------------------------------
    // {{{ DIVISION
    /**
     *  分割の指標になるKEY
     *  @var  string
     */
    protected $index_criteria_params = 'user_id';

    /**
     *  テーブル分割数
     *  @var  int
     */
    protected $division_count_dsn    = 2;

    /**
     *  テーブル分割数
     *  @var  int
     */
    protected $division_count_table  = 100;

    /**
     *  テーブル接尾のフォーマット
     *  @var  string
     */
    protected $format_suffix_dsn     = '_%d';

    /**
     *  テーブル接尾のフォーマット
     *  @var  string
     */
    protected $format_suffix_table   = '_%02d';

    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ getDivisionDSNIndex
    /**
     *  分割の指標になる値を返す
     *
     *  @param   Cascade_DB_Criteria  抽出条件
     *  @return  scalar               分割指標
     */
    protected /* string */
        function getDivisionDSNIndex(Cascade_DB_Criteria $criteria)
    {
        $index   = 1;
        $t_index = $this->getDivisionTableIndex($criteria);
        $range   = floor($this->division_count_table / $this->division_count_dsn);
        while (1) {
            if ($t_index < $range * $index) {
                break;
            }
            $index ++;
        }
        return $index;
    }
    // }}}
    // {{{ getDivisionTableIndex
    /**
     *  分割の指標になる値を返す
     *
     *  @param   Cascade_DB_Criteria  抽出条件
     *  @return  scalar               分割指標
     */
    protected /* int */
        function getDivisionTableIndex(Cascade_DB_Criteria $criteria)
    {
        $value = $this->getCriteriaValue($criteria);
        $division_index = ($value % $this->division_count_table);
        return $division_index;
    }
    // }}}
    // {{{ getCriteriaValue
    /**
     *  分割の指標になる値を返す
     *
     *  @param   Cascade_DB_Criteria  抽出条件
     *  @return  scalar               分割指標
     */
    protected final /* int */
        function getCriteriaValue(Cascade_DB_Criteria $criteria)
    {
        $value = NULL;
        $name  = $this->index_criteria_params;
        foreach (array($criteria->params, $criteria->hint) as $tmp) {
            if (is_array($tmp) && array_key_exists($name, $tmp)) {
                $value = $tmp[$name];
                break;
            }
        }
        if (is_scalar($value) === FALSE) {
            $ex_msg = 'Undefined criteria params for ShardSelector {df, index} %s %s';
            $ex_msg = sprintf($ex_msg, $criteria->df_name, $name);
            throw new Cascade_Exception_DBException($ex_msg);
        }
        return $value;
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ getDSNSuffix
    /**
     *  DSNの接尾文字を取得する
     *
     *  @param   Cascade_DB_Criteria  抽出条件
     *  @return  string               テーブル名の接尾文字
     */
    public /* string */
        function getDSNSuffix(Cascade_DB_Criteria $criteria)
    {
        $suffix = '';
        if ($this->format_suffix_dsn) {
            $index  = $this->getDivisionDSNIndex($criteria);
            $suffix = sprintf($this->format_suffix_dsn, $index);
        }
        return $suffix;
    }
    // }}}
    // {{{ getTableNameSuffix
    /**
     *  テーブル名の接尾文字を取得する
     *
     *  @param   Cascade_DB_Criteria  抽出条件
     *  @return  string               テーブル名の接尾文字
     */
    public /* string */
        function getTableNameSuffix(Cascade_DB_Criteria $criteria)
    {
        $suffix = '';
        if ($this->format_suffix_table) {
            $index  = $this->getDivisionTableIndex($criteria);
            $suffix = sprintf($this->format_suffix_table, $index);
        }
        return $suffix;
    }
    // }}}
};
