<?php
/**
 *  CustomGateway.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Proxy
 */

/**
 *  Cascade_Proxy_CustomGateway
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Proxy
 *
 *  @property-read  string  $schema_name  スキーマ
 *  @property-read  string  $namespace    スキーマ:名前空間
 *  @property-read  string  $identifier   スキーマ:識別子
 *  @property-read  Cascade_Proxy_Delegator  $delegate    セッション
 *  @property-read  Cascade_Proxy_Delegator  $session     セッション($delegateの別名)
 */
abstract class Cascade_Proxy_CustomGateway
    extends    Cascade_Proxy_Gateway
{
    // ----[ Class Constants ]----------------------------------------
    const PROP_NAME_SESSION = 'session';

    // ----[ Properties ]---------------------------------------------
    protected $schema_name = NULL;
    protected $namespace   = NULL;
    protected $identifier  = NULL;
    protected $delegate    = NULL;

    // ----[ Properties ]---------------------------------------------
    /**
     *  Read-Only propertiies names
     *  @var array
     */
    protected /* array */ $ro_props = array(
        'schema_name',
        'namespace',
        'identifier',
        'delegate',
    );

    // ----[ Magic Methods ]------------------------------------------
    // {{{ __construct
    /**
     *  コンストラクタ
     *
     *  @param  Cascade_Facade_Facade  セッション
     *  @param  string                   スキーマ名
     */
    public /** void */
        function __construct(Cascade_Facade_Facade $session,
                             /* string */          $schema_name)
    {
        parent::__construct($session);

        // 基本情報を内部変数に格納
        $this->schema_name = $schema_name;
        $this->delegate    = new Cascade_Proxy_PassThroughGateway($session, $schema_name);
        list(
            $this->namespace,
            $this->identifier
        ) = Cascade_System_Schema::parseSchemaName($schema_name);

        // トリガーの設置
        $this->delegate->setBeforeTrigger($this, 'callSessionBefore');
        $this->delegate->setAfterTrigger ($this, 'callSessionAfter');
    }
    // }}}
    // {{{ __get
    /**
     *  ゲートウェイの呼び出し専用の変数値を実装する
     *
     *  @param   string  変数名
     *  @return  mixed   変数値
     */
    public final /* mixed */
        function __get(/* string */ $name)
    {
        // Facade取得
        if ($name === self::PROP_NAME_SESSION) {
            return $this->delegate;
        }
        // 読み込み専用のプロパティ値
        if (in_array($name, $this->ro_props)) {
            return $this->$name;
        }
        // エラー
        $err_msg = 'Undefined property: %s::$%s';
        $err_msg = sprintf($err_msg, get_class($this), $name);
        trigger_error($err_msg, E_USER_NOTICE);
    }
    // }}}
    // {{{ __call
    /**
     *  アクセス不可関数を呼び出したときに呼ばれる
     *
     *  @param   string  呼び出し関数名
     *  @param   string  関数に渡される引数を配列に格納した値
     *  @return  mixed   実行結果
     */
    public final /* mixed */
        function __call(/* string */ $method,
                        /* array  */ $args)
    {
        return call_user_func_array(array($this->delegate, $method), $args);
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
        function callSessionBefore(/* string */ $method,
                                   /* array  */ $args)
    {
        return NULL;
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
    public /* void */
        function callSessionAfter(/* string */ $method,
                                  /* array  */ $args,
                                  /* mixed  */ $result)
    {}
    // }}}
};