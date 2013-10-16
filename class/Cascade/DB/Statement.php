<?php
/**
 *  Statement.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_DB
 */

/**
 *  [抽象クラス] 問い合わせステートメント
 *
 *  データ問い合わせに必要な情報を構築する。<br/>
 *  実際のデータ問い合わせはドライバーを介して行う。
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_DB
 */
abstract class Cascade_DB_Statement
    extends    Cascade_Object
{
    // ----[ Properties ]---------------------------------------------
    protected /* Cascade_DB_Criteria   */ $criteria    = NULL;
    protected /* Cascade_DB_DataFormat */ $data_format = NULL;

    // ----[ Magic Methods ]------------------------------------------
    // {{{ __construct
    /**
     *  コンストラクタ
     *
     *  @param  Cascade_DB_Criteria  データ抽出条件
     */
    public /* void */
        function __construct(Cascade_DB_Criteria $criteria)
    {
        parent::__construct();
        if ($criteria->df_name === NULL) {
            $ex_msg = 'Not found DataFormat Name in Criteria {criteria} %s';
            $ex_msg = sprintf($ex_msg, Cascade_System_Util::export($criteria, TRUE));
            throw new Cascade_Exception_DBException($ex_msg);
        }
        $this->data_format = Cascade::getDataFormat($criteria->df_name);
        $this->criteria    = $criteria;
    }
    // }}}
    // ----[ Abstraction Methods ]------------------------------------
    // {{{ prepare
    /**
     *  実行に必要な準備をする
     *
     *  ステートメント実行に必要となる準備を行う。
     */
    abstract protected /* void */
        function prepare(/* void */);
    // }}}
    // {{{ preform
    /**
     *  ステートメントを実行する
     *
     *  実行準備が整ったステートメントを実行する。
     */
    abstract protected /* void */
        function perform(/* void */);
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ getCriteria
    /**
     *  データ抽出条件を取得する
     *
     *  関連づけられているデータ抽出条件を取得する。
     *
     *  @return  Cascade_DB_Criteria  データ抽出条件
     */
    public final /* Cascade_DB_Criteria */
        function getCriteria(/* void */)
    {
        return $this->criteria;
    }
    // }}}
    // {{{ getDataFormat
    /**
     *  データ・フォーマットを取得する
     *
     *  関連づけられているデータ・フォーマットを取得する。
     *
     *  @return  Cascade_DB_DataFormat  データ・フォーマット
     */
    public final /* Cascade_DB_DataFormat */
        function getDataFormat(/* void */)
    {
        return $this->data_format;
    }
    // }}}
    // {{{ execute
    /**
     *  ステートメントを実行する
     *
     *  ステートメントを実行して、その実行結果を返す。
     *
     *  @param   boolean  (optional) TRUE:割り込み処理を無視する
     *  @return  mixed    実行結果
     */
    public final /* mixed */
        function execute(/* boolean */ $ignore_icptr = FALSE)
    {
        if ($ignore_icptr === FALSE) {
            $invocation = new Cascade_AOP_Invocation($this);
            return $invocation->proceed();
        }
        $this->prepare();
        return $this->perform();
    }
    // }}}
};
