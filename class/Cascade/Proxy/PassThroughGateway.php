<?php
/**
 *  PassThroughGateway.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Proxy
 */

/**
 *  ファサードに処理を委譲する
 *
 *  受け取った情報をそのまま関数委譲する<br />
 *  (ゲートウェイ内部で特別な情報操作処理は行わない)
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Proxy
 */
final class Cascade_Proxy_PassThroughGateway
    extends Cascade_Proxy_Gateway
{
    // ----[ Properties ]---------------------------------------------
    /**
     *  スキーマ名
     *  @var  string
     */
    protected $schema_name = NULL;

    // ----[ Magic Methods ]------------------------------------------
    // {{{ __construct
    /**
     *  コンストラクタ
     *
     *  @param  Cascade_Facade_Facade  ファサード
     *  @param  string                 スキーマ
     */
    public /** void */
        function __construct(Cascade_Facade_Facade $session,
                             /* string */          $schema_name)
    {
        parent::__construct($session);
        $this->schema_name = $schema_name;
    }
    // }}}
    // {{{ __call
    /**
     *  アクセス不可関数を呼び出したときに呼ばれる
     *
     *  コンストラクタで渡された委譲対象のインスタンスが持つ関数に
     *  処理を委譲する目的で実装している。
     *
     *  @param   string  呼び出し関数名
     *  @param   string  関数に渡される引数を配列に格納した値
     *  @return  mixed   実行結果
     */
    public /** mixed */
        function __call(/** string */ $method,
                        /** array  */ $args)
    {
        $args = array_merge(array($this->schema_name), $args);
        return parent::__call($method, $args);
    }
    // }}}
};