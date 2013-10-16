<?php
/**
 *  Interceptor.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_AOP
 */

/**
 *  [抽象クラス] 割り込み処理
 *
 *  割り込み処理インターフェースを使用した抽象クラス定義。
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_AOP
 */
abstract class Cascade_AOP_Interceptor
    extends    Cascade_Object
{
    // ----[ Class Constants ]----------------------------------------
    /** 実行レベル1 */
    const IS_LEVEL_1 = 1;
    /** 実行レベル2 */
    const IS_LEVEL_2 = 2;
    /** 実行レベル3 */
    const IS_LEVEL_3 = 3;
    /** 実行レベル4 */
    const IS_LEVEL_4 = 4;
    /** 実行レベル5 */
    const IS_LEVEL_5 = 5;

    // ----[ Class Constants ]----------------------------------------
    /** 割り込み処理に定義可能な最大レベル */
    const MIN_LEVEL = self::IS_LEVEL_1;
    /** 割り込み処理に定義可能な最小レベル */
    const MAX_LEVEL = self::IS_LEVEL_5;

    // ----[ Properties ]---------------------------------------------
    /**
     *  実行レベル
     *  @var  int
     */
    protected $level = NULL;

    // ----[ Abstraction Methods ]------------------------------------
    // {{{ isEnable
    /**
     *  割り込み処理を実行可能か確認する
     *
     *  @param   Cascade_AOP_Invocation  データ問い合わせの呼び出し定義
     *  @return  boolean                 TRUE:割り込み処理実行
     */
    abstract public /* boolean */
        function isEnable(Cascade_AOP_Invocation $invocation);
    // }}}
    // {{{ invoke
    /**
     *  割り込み処理を起動する
     *
     *  データ問い合わせへの割り込み処理を起動する。<br/>
     *  割り込み処理は自由度が高いため実装には気をつけてください。
     *  <ul>
     *    <li>データ抽出条件、データ問い合わせ結果に応じた処理が定義できる。</li>
     *    <li>データ抽出条件を加工する事ができる。</li>
     *    <li>データ問い合わせ結果を加工する事ができる。</li>
     *  </ul>
     *
     *  @param   Cascade_AOP_Invocation  データ問い合わせの呼び出し定義
     *  @return  mixed                   データ問い合わせ結果
     */
    abstract public /* mixed */
        function invoke(Cascade_AOP_Invocation $invocation);
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ getLevel
    /**
     *  実行レベルを取得する
     *
     *  割り込み処理に設定された実行レベルを取得する。<br/>
     *  実行レベルの指定がない、もしくは設定範囲値が異常な場合は例外が発生する
     *
     *  @return  int  実行レベル
     */
    public final /* int */
        function getLevel(/* void */)
    {
        if (is_int($this->level) === FALSE
            || $this->level < self::MIN_LEVEL
            || $this->level > self::MAX_LEVEL) {
            $ex_msg = 'Illegal interceptor level {class, level} %s %s';
            $ex_msg = sprintf($ex_msg, get_class($this), $this->level);
            throw new Cascade_Exception_AOPException($ex_msg);
        }
        return $this->level;
    }
    // }}}
}