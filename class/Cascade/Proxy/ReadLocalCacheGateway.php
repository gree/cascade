<?php
/**
 *  ReadLocalCacheGateway.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Proxy
 */

/**
 *  Cascade_Proxy_ReadLocalCacheGateway
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Proxy
 *
 *  @property-read  string  $schema_name  スキーマ
 *  @property-read  string  $namespace    スキーマ:名前空間
 *  @property-read  string  $identifier   スキーマ:識別子
 *  @property-read  Cascade_Proxy_Delegator  $delegate    ファサード
 *  @property-read  Cascade_Proxy_Delegator  $session     ファサード($delegateの別名)
 */
class Cascade_Proxy_ReadLocalCacheGateway
    extends Cascade_Proxy_CustomGateway
{
    // ----[ Magic Methods ]------------------------------------------
    // {{{ __construct
    /**
     *  コンストラクタ
     *
     *  @param  Cascade_Facade_Facade  ファサード
     *  @param  string                 スキーマ名
     */
    public /** void */
        function __construct(Cascade_Facade_Facade $session,
                             /* string */          $schema_name)
    {
        parent::__construct($session, $schema_name);
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ callSessionBefore
    /**
     *  ファサード関数呼び出し前に実行するトリガーを設定
     *
     *  @param   string  関数名
     *  @param   array   関数に渡される引数を配列に格納した値
     *  @return  mixed   既に結果がある場合に返す
     */
    public /** mixed */
        function callSessionBefore($method, $args)
    {
        // 更新系の操作は制限する
        if ($method == 'execute') {
            $ex_msg = 'Unsupported Method {method} %s';
            $ex_msg = sprintf($ex_msg, $method);
            throw new Cascade_Exception_ProxyException($ex_msg);
        }
        // キャッシュデータを参照する
        $ckey = md5(serialize(array($method, $args)));
        if (FALSE !== ($cdata = $this->getCacheDriver()->get($ckey))) {
            return $cdata;
        }
        return parent::callSessionBefore($method, $args);
    }
    // }}}
    // {{{ callSessionAfter
    /**
     *  ファサード関数呼び出し後に実行するトリガーを設定
     *
     *  @param   string  関数名
     *  @param   array   関数に渡される引数を配列に格納した値
     *  @param   mixed   実行結果
     */
    public /** mixed */
        function callSessionAfter($method, $args, $result)
    {
        // キャッシュデータに保存する
        $ckey = md5(serialize(array($method, $args)));
        $this->getCacheDriver()->set($ckey, $result);
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ getCacheDriver
    /**
     *  キャッシュ操作ドライバーを取得する
     *
     *  @return  Cascade_Driver_Driver  ドライバー
     */
    protected /* Cascade_Driver_Driver */
        function getCacheDriver()
    {
        $args = array($namespace = '::'.$this->schema_name);
        return Cascade_Driver_Factory::getInstance(Cascade::DRIVER_APC, $args);
    }
    // }}}
};