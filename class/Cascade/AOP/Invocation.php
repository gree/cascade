<?php
/**
 *  Invocation.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_DB
 */

/**
 *  データ問い合わせの呼び出し定義
 *
 *  データ問い合わせ処理に必要な情報を保持する。<br/>
 *  割り込み処理はデータ・フォーマットから取得される。
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_AOP
 */
final class Cascade_AOP_Invocation
    extends Cascade_Object
{
    // ----[ Class Constants ]----------------------------------------
    /** 割り込み処理に定義可能な最大レベル */
    const ICPTOR_LEVEL_MIN = Cascade_AOP_Interceptor::MIN_LEVEL;
    /** 割り込み処理に定義可能な最小レベル */
    const ICPTOR_LEVEL_MAX = Cascade_AOP_Interceptor::MAX_LEVEL;

    // ----[ Properties ]---------------------------------------------
    /**
     *  割り込み処理一覧
     *  @var array
     */
    protected $icptrs = array();

    /**
     *  データ問い合わせステートメント
     *  @var Cascade_Statement
     */
    protected $stmt = NULL;

    // ----[ Magic Methods ]------------------------------------------
    // {{{ __construct
    /**
     *  コンストラクタ
     *
     *  インスタンスの内部変数を初期化する。
     *
     *  @see    Cascade_DB_Statement
     *  @param  Cascade_DB_Statement  問い合わせステートメント
     */
    public /* void */
        function __construct(Cascade_DB_Statement $stmt)
    {
        parent::__construct();
        // Initializes internal cursor
        $this->cur_icptr_pos   = 0;
        $this->cur_icptr_level = self::ICPTOR_LEVEL_MIN;
        // Registers interceptors
        foreach ($stmt->getDataFormat()->getInterceptors() as $_icptr) {
            $this->icptrs[$_icptr->getLevel()][] = $_icptr;
        }
        $this->stmt = $stmt;
    }
    // }}}
    // {{{ getStatement
    /**
     *  問い合わせステートメントを取得する
     *
     *  関連づけられている問い合わせステートメントを取得する
     *
     *  @return  Cascade_Core_Statement  問い合わせステートメント
     */
    public /* Cascade_Core_Statement */
        function getStatement(/* void */)
    {
        return $this->stmt;
    }
    // }}}
    // {{{ proceed
    /**
     *  呼び出し定義を実行する
     *
     *  コンストラクタで指定された、問い合わせステートメントを元にして
     *  呼び出し処理を実行する。<br/>
     *
     *  @return  mixed  呼び出し実行結果
     */
    public /* mixed */
        function proceed(/* void */)
    {
        // 割り込み処理を実行
        $pos   =& $this->cur_icptr_pos;
        $level =& $this->cur_icptr_level;
        while ($level <= self::ICPTOR_LEVEL_MAX) {
            if (isset($this->icptrs[$level][$pos])) {
                if ($this->icptrs[$level][$pos]->isEnable($this)) {
                    return $this->icptrs[$level][$pos++]->invoke($this);
                }
                $pos    ++;
            } else {
                $level  ++;
                $pos   = 0;
            }
        }
        // 問い合わせステートメントを実行
        return $this->stmt->execute($ignore_icptr = TRUE);
    }
    // }}}
};