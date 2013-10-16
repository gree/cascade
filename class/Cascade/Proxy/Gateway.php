<?php
/**
 *  Gateway.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Proxy
 */

/**
 *  ゲートウェイの基底抽象クラス
 *
 *  全てのゲートウェイは、派生クラスとして定義される
 *
 *  @package  Cascade_Proxy
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 */
abstract class Cascade_Proxy_Gateway
    extends    Cascade_Object
{
    // ----[ Class Constants ]----------------------------------------
    const TRIGGER_BEFORE   = 'before';
    const TRIGGER_AFTER    = 'after';

    const CALLBACK_OBJECT  = 'object';
    const CALLBACK_METHOD  = 'method';
    const CALLBACK_METHODS = 'methods';

    // ----[ Properties ]---------------------------------------------
    protected /* array  */ $delegate = array(
        self::CALLBACK_OBJECT  => NULL,
        self::CALLBACK_METHODS => array(),
    );
    protected /* array  */ $trigger = array(
        self::TRIGGER_BEFORE   => NULL,
        self::TRIGGER_AFTER    => NULL,
    );

    // ----[ Magic Methods ]------------------------------------------
    // {{{ __construct
    /**
     *  コンストラクタ
     *
     *  @param  object  委譲するクラス・インスタンス
     */
    public /* void */
        function __construct(/* object */ $class)
    {
        parent::__construct();
        try {
            $ref_class = new ReflectionClass($class);
            $delegate[self::CALLBACK_OBJECT] = $class;
            foreach ($ref_class->getMethods(ReflectionMethod::IS_PUBLIC) as $ref_method) {
                $method = $ref_method->getName();
                $delegate[self::CALLBACK_METHODS][$method] = $ref_method;
            }
            $this->delegate = $delegate;
        } catch (Exception $ex) {
            throw new Cascade_Exception_ProxyException($ex->getMessage(), $ex->getCode());
        }
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
    public /* mixed */
        function __call(/* string */ $method,
                        /* array  */ $args)
    {
        // 委譲対象関数が存在するか確認
        if (isset($this->delegate[self::CALLBACK_METHODS][$method]) === FALSE) {
            $ex_msg     = 'Fatal error: Call to undefined method %s::%s()';
            $class_name = get_class($this->delegate[self::CALLBACK_OBJECT]);
            $ex_msg     = sprintf($ex_msg, $class_name, $method);
            trigger_error($ex_msg, E_USER_ERROR);
            throw new Cascade_Exception_ProxyException($ex_msg);
        }
        try {
            // トリガー起動 :: 委譲関数実行前
            if ($this->trigger[self::TRIGGER_BEFORE] !== NULL) {
                $params     = array($method, $args);
                $ref_method = $this->trigger[self::TRIGGER_BEFORE][self::CALLBACK_METHOD];
                $object     = $this->trigger[self::TRIGGER_BEFORE][self::CALLBACK_OBJECT];
                if (NULL !== ($result = $ref_method->invokeArgs($object, $params))) {
                    return $result;
                }
            }
            // 委譲関数の呼び出し
            $ref_method = $this->delegate[self::CALLBACK_METHODS][$method];
            $object     = $this->delegate[self::CALLBACK_OBJECT];
            $result     = $ref_method->invokeArgs($object, $args);
            // トリガー起動 :: 委譲関数実行後
            if ($this->trigger[self::TRIGGER_AFTER] !== NULL) {
                $params     = array($method, $args, $result);
                $ref_method = $this->trigger[self::TRIGGER_AFTER][self::CALLBACK_METHOD];
                $object     = $this->trigger[self::TRIGGER_AFTER][self::CALLBACK_OBJECT];
                $ref_method->invokeArgs($object, $params);
            }
        } catch (Cascade_Exception_Exception $ex) {
            throw $ex;
        } catch (Exception $ex) {
            $ex_msg  = $ex->getMessage();
            $ex_code = $ex->getCode();
            throw new Cascade_Exception_ProxyException($ex_msg, $ex_code);
        }
        // 実行結果を返す
        return $result;
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ getInstance
    /**
     *  インスタンスを取得する
     *
     *  @param   string                 スキーマ名
     *  @return  Cascade_DB_Gateway  インスタンス
     */
    public static /* string */
        function getInstance(/* string */ $schema_name)
    {
        static $instances = array();

        if (isset($instances[$schema_name]) === FALSE) {
            $instances[$schema_name] = self::createInstance($schema_name);
        }
        return $instances[$schema_name];
    }
    // }}}
    // {{{ createInstance
    /**
     *  インスタンスを生成する
     *
     *  @param   string                 スキーマ名
     *  @return  Cascade_DB_Gateway  インスタンス
     */
    public static /* string */
        function createInstance(/* string */ $schema_name)
    {
        // 基本情報の取得
        $data_format = Cascade::getDataFormat($schema_name);
        $facade      = Cascade_Facade_Facade::getInstance($data_format);
        $class_name = Cascade_System_Schema::getGatewayClassName($schema_name);
        // インスタンス作成
        $instance = class_exists($class_name)
            ? new $class_name($facade, $schema_name)
            : new Cascade_Proxy_PassThroughGateway($facade, $schema_name);
        if (($instance instanceof Cascade_Proxy_Gateway) === FALSE) {
            $ex_msg = 'Invalid a Instance of Gateway {class} %s';
            $ex_msg = sprintf($ex_msg, $class_name);
            throw new Cascade_Exception_Exception($ex_msg);
        }
        // 作成したインスタンスを返す
        return $instance;
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ setTrigger
    /**
     *  委譲関数呼び出し前後にトリガーを設定する
     *
     *  @param  int     トリガー種別
     *  @param  mixed   トリガークラス
     *  @param  string  トリガー関数
     */
    protected /* void */
        function setTrigger(/* int    */ $type,
                            /* mixed  */ $class,
                            /* string */ $method)
    {
        // トリガー種別確認
        if (self::TRIGGER_BEFORE !== $type && self::TRIGGER_AFTER !== $type) {
            $ex_msg = 'Unsupported the Trigger type {type} %d';
            $ex_msg = sprintf($ex_msg, $type);
            throw new Cascade_Exception_ProxyException($ex_msg);
        }
        // トリガー情報を構築
        $trigger = NULL;
        try {
            $ref_method = new ReflectionMethod($class, $method);
            if ($ref_method->isPublic()) {
                if ($ref_method->isStatic()) {
                    $trigger = array(
                        self::CALLBACK_OBJECT => NULL,
                        self::CALLBACK_METHOD => $ref_method,
                    );
                } else if (is_object($class)) {
                    $trigger = array(
                        self::CALLBACK_OBJECT => $class,
                        self::CALLBACK_METHOD => $ref_method,
                    );
                }
            }
        } catch (Exception $ex) {
            $ex_msg = 'Fatal error: Undefined method %s::%s';
            $ex_msg = sprintf($ex_msg, $class, $method);
            throw new Cascade_Exception_ProxyException($ex_msg);
        }
        // アクセス権等でトリガーが設定できない場合
        if ($trigger === NULL) {
            $ex_msg = 'Could not set a Trigger {class, method} %s %s';
            $ex_msg = sprintf($ex_msg, $class, $method);
            throw new Cascade_Exception_ProxyException($ex_msg);
        }
        // トリガーを設定
        $this->trigger[$type] = $trigger;
    }
    // }}}
    // {{{ setBeforeTrigger
    /**
     *  委譲関数呼び出し前にトリガーを設定する
     *
     *  @param  mixed   トリガークラス
     *  @param  string  トリガー関数
     */
    public /* void */
        function setBeforeTrigger(/* mixed  */ $class,
                                  /* string */ $method)
    {
        $this->setTrigger(self::TRIGGER_BEFORE, $class, $method);
    }
    // }}}
    // {{{ setAfterTrigger
    /**
     *  委譲関数呼び出し後にトリガーを設定する
     *
     *  @param  mixed   トリガークラス
     *  @param  string  トリガー関数
     */
    public /* void */
        function setAfterTrigger(/* mixed  */ $class,
                                 /* string */ $method)
    {
        $this->setTrigger(self::TRIGGER_AFTER, $class, $method);
    }
    // }}}
};