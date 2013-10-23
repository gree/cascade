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
 *
 *  [Common Methods]
 *
 *  @method mixed get($key, $hint_or_section = NULL, $use_master_or_name = false)
 *      hint and use_master parameters are used for `Cascade_Facade_SQL`. section and name are used for Cascade_Facade_Config.
 *
 *  @method mixed mget($keys, $hint = null, $use_master = false)
 *      hint and use_master parameter are used for `Cascade_Facade_SQL`. `Cascade_Facade_Config` does not support this method.
 *
 *  [Facade/KVS methods]
 *  @see Cascade_Facade_KVS
 *
 *  @method boolean add($key, $value, $expiration = 0)
 *  @method boolean set($key, $value, $expiration = 0)
 *  @method boolean replace($key, $value, $expiration = 0)
 *  @method boolean cas($cas_token,  $key, $value, $expiration = 0)
 *  @method boolean delete($key)
 *  @method int increment($key, $offset = 1)
 *  @method int decrement($key, $offset = 1)
 *  @method int getErrorCode()
 *  @method string getErrorMessage()
 *
 *  [Facade/SQL methods]
 *  @see Cascade_Facade_SQL
 *
 *  @method mixed getEx($extra_dsn, $key, $hint = NULL, $use_master = false)
 *  @method mixed mgetEx($extra_dsn, $keys, $hint = null, $use_master = false)
 *  @method mixed findFirst($stmt_name, $params = null, $hint = null, $use_master = false)
 *  @method mixed findFirstEx($extra_dsn, $stmt_name, $params = null, $hint = null, $use_master = false)
 *  @method mixed find($stmt_name, $params = null, $offset = null, $limit = null, $hint = null,  $use_master = false)
 *  @method mixed findEx($extra_dsn, $stmt_name, $params = null, $offset = null, $limit = null, $hint = null, $use_master = false)
 *  @method mixed toValue($stmt_name, $params = null, $hint = null, $use_master = false])
 *  @method mixed toValueEx($extra_dsn, $stmt_name, $params = null, $hint = null, $use_master = false)
 *  @method mixed execute($stmt_name, $params = null, $hint = null)
 *  @method int lastInsertId($hint = null)
 *  @method boolean beginTransaction($hint = null)
 *  @method boolean commit($hint = null)
 *  @method boolean rollback($hint = null)
 *  @method int createDb($hint = null)
 *
 *  [Facade/Config methods]
 *  @see Cascade_Facade_Config
 *
 *  @method mixed getAll()
 *  @method int count()
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