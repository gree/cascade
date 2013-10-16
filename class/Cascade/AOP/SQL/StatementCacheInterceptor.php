<?php
/**
 *  StatementCacheInterceptor.php
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_AOP
 *  @subpackage  SQL
 */

/**
 *  Cascade_AOP_SQL_StatementCacheInterceptor
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_AOP
 *  @subpackage  SQL
 */
final class Cascade_AOP_SQL_StatementCacheInterceptor
    extends Cascade_AOP_Interceptor
{
    // ----[ Properties ]---------------------------------------------
    /**
     *  実行レベル
     *  @var  int
     */
    protected $level = self::IS_LEVEL_2;

    // ----[ Properties ]---------------------------------------------
    /**
     *  キャッシュ・データ格納領域
     *  @var  array
     */
    protected static $cached_data = array();

    // ----[ Methods ]------------------------------------------------
    // {{{ isEnable
    /**
     *  割り込み処理を実行可能か確認する
     *
     *  @param   Cascade_AOP_Invocation  データ問い合わせの呼び出し定義
     *  @return  boolean                 TRUE:割り込み処理実行
     */
    public /* boolean */
        function isEnable(Cascade_AOP_Invocation $invocation)
    {
        $criteria = $invocation->getStatement()->getCriteria();
        if (($criteria instanceof Cascade_DB_SQL_Criteria) === FALSE) {
            $ex_msg = 'Could not use a Interceptor {class} %s';
            $ex_msg = sprintf($ex_msg, __CLASS__);
            throw new Cascade_Exception_Exception($ex_msg);
        }
        return TRUE;
    }
    // }}}
    // {{{ invoke
    /**
     *  割り込み処理を起動する
     *
     *  @param   Cascade_AOP_Invocation  データ問い合わせの呼び出し定義
     *  @return  mixed                   データ問い合わせ結果
     */
    public /* mixed */
        function invoke(Cascade_AOP_Invocation $invocation)
    {
        $criteria    = $invocation->getStatement()->getCriteria();
        $data_format = $invocation->getStatement()->getDataFormat();
        // キャッシュ機能がOFFになっている場合
        if ($data_format->isDisableCacheMode($criteria)) {
            return $invocation->proceed();
        }
        // 条件に該当する結果がキャッシュされているか確認
        $ident = md5(serialize($criteria));
        if ($criteria->type !== $criteria->getConstant('TYPE_IS_EXEC')) {
            if (NULL !== ($result = $this->get($ident))) {
                return $result;
            }
        }
        // 実行処理
        $result = $invocation->proceed();
        // 実行結果値をキャッシュする (更新系はキャッシュデータを削除)
        if ($criteria->type !== $criteria->getConstant('TYPE_IS_EXEC')) {
            $this->set($ident, $result);
        } else {
            $this->refresh();
        }
        // 実行結果値を返す
        return $result;
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ get
    /**
     *  指定KEYに格納されたデータを取得する
     *
     *  @param   scalar  キャッシュKEY
     *  @return  mixed   格納データ
     */
    protected /* mixed */
        function get(/* scalar */ $key)
    {
        // KEYを確認する
        if (is_scalar($key) === FALSE) {
            $ex_msg  = 'Z_TYPE of a key is not scalor {type} %s';
            $ex_msg  = sprintf($ex_msg, gettype($key));
            throw new Cascade_Exception_SystemException($ex_msg);
        }
        // 格納データを返す
        $key = (string) $key;
        return isset(self::$cached_data[$key]) ? self::$cached_data[$key] : NULL;
    }
    // }}}
    // {{{ set
    /**
     *  指定KEYにデータ格納する
     *
     *  @param   scalar  キャッシュKEY
     *  @param   mixed   格納データ
     */
    protected /* void */
        function set(/* scalar */ $key,
                     /* mixed  */ $value)
    {
        // KEYを確認する
        if (is_scalar($key) === FALSE) {
            $ex_msg  = 'Z_TYPE of a key is not scalor {type} %s';
            $ex_msg  = sprintf($ex_msg, gettype($key));
            throw new Cascade_Exception_SystemException($ex_msg);
        }
        // データを格納する
        $key = (string) $key;
        self::$cached_data[$key] = $value;
    }
    // }}}
    // {{{ refresh
    /**
     *  格納データを全て消去する
     */
    protected /* void */
        function refresh(/* void */)
    {
        self::$cached_data = array();
    }
    // }}}
};