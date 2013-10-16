<?php
/**
 *  DataFormat.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_DB
 */

/**
 *  [抽象クラス] データ・フォーマット
 *
 *  データ・フォーマットインターフェースを使用した抽象クラス定義。
 *
 *  @package  Cascade_DB
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 */
abstract class Cascade_DB_DataFormat
    extends    Cascade_Object
{
    // ----[ Class Constants ]----------------------------------------
    const EXTRA_PROP_NO_CACHE           = 'no-cache';
    const EXTRA_STATIC_PROP_DQ_CALLBACK = 'd-query-callback';

    // ----[ Properties ]---------------------------------------------
    // {{{ EXTRA-PROP
    /**
     *  追加プロパティ格納領域
     *  @var array
     */
    protected        $extra_prop        = array();
    // {{{ DRIVER
    /**
     *  接続ドライバー識別子
     *  データベースへ問い合わせをに用いるドライバーを指定する。
     *  @var int
     */
    protected $driver_type = 0x01;

    /**
     *  追加静的プロパティ格納領域
     *  @var array
     */
    protected static $extra_static_prop = array();
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ getInstance
    /**
     *  インスタンスを取得する
     *
     *  @param   string                 スキーマ名
     *  @return  Cascade_DB_DataFormat  インスタンス
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
     *  @return  Cascade_DB_DataFormat  インスタンス
     */
    public static /* string */
        function createInstance(/* string */ $schema_name)
    {
        // クラス名の取得
        $class_name = Cascade_System_Schema::getDataFormatClassName($schema_name);
        if (class_exists($class_name) === FALSE) {
            $ex_msg = 'Not found DataFormat {schema_name, class_name} %s %s';
            $ex_msg = sprintf($ex_msg, $schema_name, $class_name);
            throw new Cascade_Exception_Exception($ex_msg);
        }
        // インスタンス作成
        $instance = new $class_name;
        if (($instance instanceof Cascade_DB_DataFormat) === FALSE) {
            $ex_msg = 'Invalid a Instance of DataFormat {class} %s';
            $ex_msg = sprintf($ex_msg, $class_name);
            throw new Cascade_Exception_Exception($ex_msg);
        }
        // 作成したインスタンスを返す
        return $instance;
    }
    // }}}
    // {{{ getInterceptors
    /**
     *  インターセプターを取得する
     *
     *  データ種別に応じた割り込み処理実装クラスのインスタンスを取得する。<br/>
     *  割り込み処理はデータ・フォーマット毎に定義される。
     *
     *  @see     Cascade_DB_Interceptor
     *  @return  array  インターセプター・リスト
     */
    public final /* array */
        function getInterceptors(/* void */)
    {
        $icptrs = array();
        foreach ($this->interceptors as $class_name) {
            $icptr = new $class_name;
            $icptrs[] = $icptr;
        }
        return $icptrs;
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ setExtraProperty
    /**
     *  追加プロパティを格納する
     *
     *  @param  string  プロパティ名
     *  @param  mixed   プロパティ値
     */
    public final /* void */
        function setExtraProperty(/* string */ $name,
                                  /* mixed  */ $value)
    {
        $this->extra_prop[$name] = $value;
    }
    // }}}
    // {{{ getExtraProperty
    /**
     *  追加プロパティを取得する
     *
     *  @param   string  プロパティ名
     *  @return  mixed   プロパティ値
     */
    public final /* mixed */
        function getExtraProperty(/* string */ $name)
    {
        return array_key_exists($name, $this->extra_prop)
            ? $this->extra_prop[$name]
            :  NULL;
    }
    // }}}
    // {{{ setExtraStaticProperty
    /**
     *  静的追加プロパティを格納する
     *
     *  @param  string  プロパティ名
     *  @param  mixed   プロパティ値
     */
    public final static /* void */
        function setExtraStaticProperty(/* string */ $name,
                                        /* mixed  */ $value)
    {
        self::$extra_static_prop[$name] = $value;
    }
    // }}}
    // {{{ getExtraStaticProperty
    /**
     *  静的追加プロパティを取得する
     *
     *  @param   string  プロパティ名
     *  @return  mixed   プロパティ値
     */
    public final static /* mixed */
        function getExtraStaticProperty(/* string */ $name)
    {
        return array_key_exists($name, self::$extra_static_prop)
            ? self::$extra_static_prop[$name]
            : NULL;
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ isDisableCacheMode
    /**
     *  キャッシュ機能がOFFモードかを確認する
     *
     *  @return  boolean  キャッシュ機能がOFFモード
     */
    public /* boolean */
        function isDisableCacheMode(Cascade_DB_Criteria $criteria)
    {
        return CASCADE_DISABLE_CACHE
            || $this->getExtraProperty(self::EXTRA_PROP_NO_CACHE);
    }
    // }}}
    // {{{ getDriverType
    /**
     *  DB接続ドライバー識別子を取得する
     *
     *  設定されているドライバー識別子を取得する。
     *
     *  @return  int  ドライバー種別
     */
    public final /* int */
        function getDriverType(/* void */)
    {
        return $this->driver_type;
    }
    // }}}
};