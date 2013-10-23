<?php
/**
 *  KVS.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Driver
 */

/**
 *  KVSを扱うドライバー抽象定義
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Driver
 */
interface   Cascade_Driver_KVS
    extends Cascade_Driver_Driver
{
    // ----[ Magic Methods ]------------------------------------------
    // {{{ __construct
    /**
     *  コンストラクタ
     *
     *  @param  string   ネームスペース
     *  @param  string   (optional) Database-Source-Name
     *  @param  boolean  (optional) 圧縮フラグ
     */
    public /* void */
        function __construct(/* string  */ $namespace,
                             /* string  */ $dsn        = NULL,
                             /* boolean */ $compressed = FALSE);
    // }}}
    // ----[ Interface Methods ]--------------------------------------
    // {{{ get
    /**
     *  指定KEYの値を取得する
     *
     *  指定のKEYに格納された値を取得する。<br/>
     *  見つかったデータに関してはCASトークンの情報を取得することができます。
     *
     *  @param   string  KEY
     *  @param   string  (optional) CASトークン
     *  @return  mixed   指定KEYに格納された値を取得する。
     *                   存在しない、もしくはエラーの場合はFALSEを返す。
     */
    public /* mixed */
        function get(/* string */  $key,
                     /* scalar */ &$cas_token = NULL);
    // }}}
    // {{{ mget
    /**
     *  複数の指定KEYの値を取得する
     *
     *  {@link get()}関数と挙動が似ていますが、<br />
     *  複数のKEYを配列型で同時に指定する事ができます。<br />
     *  見つかったデータに関してはCASトークンの情報を同時に取得することができます。
     *
     *  @param   array   KEYリスト
     *  @param   array   (optional) CASトークン・リスト
     *  @return  mixed   指定KEYに格納された値を複数取得する。
     *                   存在しない、もしくはエラーの場合はFALSEを返す。
     */
    public /* array */
        function mget(/* array */  $keys,
                      /* array */ &$cas_tokens = NULL);
    // }}}
    // ----[ Interface Methods ]--------------------------------------
    // {{{ add
    /**
     *  新規データを追加する
     *
     *  指定のKEYに新規データを追加する。<br/>
     *  既にデータが存在した場合は、処理に失敗しFALSEを返す。
     *
     *  @param   string   KEY
     *  @param   mixed   新規追加する値
     *  @param   int      (optional) データ有効期間
     *  @return  boolean  処理結果
     */
    public /* void */
        function add(/* string */ $key,
                     /* mixed  */ $value,
                     /* int    */ $expiration = 0);
    // }}}
    // {{{ set
    /**
     *  データを更新する
     *
     *  指定のKEYのデータを更新します。<br/>
     *  データが存在しない場合は、新規にデータを作成します。
     *
     *  @param   string   KEY
     *  @param   mixed   新規追加する値
     *  @param   int      (optional) データ有効期間
     *  @return  boolean  処理結果
     */
    public /* void */
        function set(/* string */ $key,
                     /* mixed  */ $value,
                     /* int    */ $expiration = 0);
    // }}}
    // {{{ replace
    /**
     *  既存データを更新する
     *
     *  指定KEYに既にあるデータを更新します。<br/>
     *  データが存在しない場合は、処理は失敗しFALSEを返します。
     *
     *  @param   string   KEY
     *  @param   mixed   新規追加する値
     *  @param   int      (optional) データ有効期間
     *  @return  boolean  処理結果
     */
    public /* void */
        function replace(/* string */ $key,
                         /* mixed  */ $value,
                         /* int    */ $expiration = 0);
    // }}}
    // {{{ cas
    /**
     *  既存データ状態を確認して更新する
     *
     *  サーバ上の既存データのCASトークンの値と、<br/>
     *  更新処理時に渡されたクライアントのCASトークンを比較して、<br/>
     *  一致すればデータを更新します。一致しない場合は処理は失敗しFALSEを返します。
     *
     *  @param   string   CASトークン
     *  @param   string   KEY
     *  @param   mixed   新規追加する値
     *  @param   int      (optional) データ有効期間
     *  @return  boolean  処理結果
     */
    public /* void */
        function cas(/* string */ $cas_token,
                     /* string */ $key,
                     /* mixed  */ $value,
                     /* int    */ $expiration = 0);
    // }}}
    // {{{ delete
    /**
     *  既存データを削除する
     *
     *  指定KEYに保存されているデータを削除する。
     *
     *  @param   string   KEY
     *  @return  boolean  処理結果
     */
    public /* void */
        function delete(/* string */ $key);
    // }}}
    // ----[ Interface Methods ]--------------------------------------
    // {{{ increment
    /**
     *  データ値を加算処理する
     *
     *  指定KEYに保存されているデータを加算処理する。
     *   - 加算結果値を結果値として返す
     *   - データが数値型として認識できない場合は0として扱う
     *   - データが存在しない場合は、処理が失敗しFALSEを返す
     *
     *  @param   string  KEY
     *  @param   int     (optional) 加算する差分値
     *  @return  int     加算後の値
     */
    public /* void */
        function increment(/* string */ $key,
                           /* int    */ $offset = 1);
    // }}}
    // {{{ decrement
    /**
     *  データ値を減算処理する
     *
     *  指定KEYに保存されているデータを減算処理する。
     *   - 減算結果値を結果値として返す
     *   - データが数値型として認識できない場合は0として扱う
     *   - データが存在しない場合は、処理が失敗しFALSEを返す
     *
     *  @param   string  データー・フォーマット
     *  @param   string  KEY
     *  @param   int     (optional) 減算する差分値
     *  @return  int     減算後の値
     */
    public /* void */
        function decrement(/* string */ $key,
                           /* int    */ $offset = 1);
    // }}}
};
