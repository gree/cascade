<?php
/**
 *  DataFormat.php
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_DB
 *  @subpackage  KVS
 */

/**
 *  KVSテーブル情報定義クラス
 *
 *  KVSテーブル情報の各種設定値を設定する。<br />
 *  KVSのデータ取得の形式、使用するドライバーもテーブル定義毎に行う。
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_DB
 *  @subpackage  KVS
 */
abstract class Cascade_DB_KVS_DataFormat
    extends    Cascade_DB_DataFormat
{
    // ----[ Class Constants ]----------------------------------------
    /** ドライバー形式 : EAC          */
    const DRIVER_EAC          = Cascade::DRIVER_EAC;
    /** ドライバー形式 : APC          */
    const DRIVER_APC          = Cascade::DRIVER_APC;
    /** ドライバー形式 : Memcached    */
    const DRIVER_MEMCACHED    = Cascade::DRIVER_MEMCACHED;
    /** ドライバー形式 : Libmemcached */
    const DRIVER_LIBMEMCACHED = Cascade::DRIVER_LIBMEMCACHED;
    /** ドライバー形式 : Flare        */
    const DRIVER_FLARE        = Cascade::DRIVER_FLARE;
    /** ドライバー形式 : Squall       */
    const DRIVER_SQUALL       = Cascade::DRIVER_SQUALL;

    // ----[ Properties ]---------------------------------------------
    // {{{ EXTRA-PROP
    /**
     *  追加プロパティ格納領域
     *  @var array
     */
    protected        $extra_prop        = array();

    /**
     *  追加静的プロパティ格納領域
     *  @var array
     */
    protected static $extra_static_prop = array();
    // }}}
    // ----[ Properties ]---------------------------------------------
    // @var string  DSN
    /**
     *  DSN
     *  標準で選択されるDSN
     *   - 動的DSNを指定する場合、{@link getDSN()}関数を拡張する。
     *  @var  string
     */
    protected $dsn          = NULL;

    /**
     *  接続ドライバー識別子
     *  KVSデータベースへ問い合わせをに用いるドライバーを指定する。
     *  @var  int
     */
    protected $driver_type  = self::DRIVER_FLARE;

    /**
     *  ネームスペース
     *  キー名に含めるネームスペース
     *  @var  string
     */
    protected $namespace    = NULL;

    /**
     *  圧縮フラグ
     *  データ圧縮を行う場合はTRUEを指定する。
     *  @var  boolean
     */
    protected $compressed   = TRUE;

    /**
     *  データ有効期限
     *  データの有効期限を設定する
     *  @var  int
     */
    protected $expiration   = 0;

    /**
     *  割り込み処理登録
     *  割り込み処理の実装クラスの登録
     *   - 割り込み処理のインスタンスは、実行毎に新規に作成(ステートレス)される。
     *  @var array
     */
    protected $interceptors = array(
        'Cascade_AOP_KVS_StatementCacheInterceptor',
    );

    // ----[ Methods ]------------------------------------------------
    // {{{ getDSN
    /**
     *  DSNを取得する
     *
     *  抽出条件からDSNを取得する。
     *
     *  @param   Cascade_DB_Criteria  データ抽出条件
     *  @return  string                    DSN
     */
    public /* string */
        function getDSN(Cascade_DB_Criteria $criteria)
    {
        return $this->dsn;
    }
    // }}}
    // {{{ getNamespace
    /**
     *  名前空間を取得する
     *
     *  @return  string  名前空間
     */
    public /* string */
        function getNamespace(/* void */)
    {
        return $this->namespace;
    }
    // }}}
    // {{{ isCompressed
    /**
     *  データ圧縮フラグを取得する
     *
     *  @return  boolean  圧縮フラグ
     */
    public final /* string */
        function isCompressed(/* void */)
    {
        return $this->compressed;
    }
    // }}}
    // {{{ getExpiration
    /**
     *  保存されたアイテムの有効期限を取得
     *
     *  @return  int  データ有効期限
     */
    public final /* int */
        function getExpiration(/* void */)
    {
        return $this->expiration;
    }
    // }}}
};
