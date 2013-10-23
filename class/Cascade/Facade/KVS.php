<?php
/**
 *  KVS.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Facade
 */

/**
 *  Cascade_Facade_KVS
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_Facade
 */
final class Cascade_Facade_KVS
    extends Cascade_Facade_Facade
{
    // ----[ Methods ]------------------------------------------------
    // {{{ get
    /**
     *  指定KEYの値を取得する
     *
     *  指定のKEYに格納された値を取得する。<br/>
     *  見つかったデータに関してはCASトークンの情報を取得することができます。
     *
     *  @param   string  スキーマ名
     *  @param   string  KEY
     *  @return  mixed   指定KEYに格納された値とCASトークンを取得する。
     *                   存在しない、もしくはエラーの場合はFALSEを返す。
     */
    public /* mixed */
        function get(/* string  */ $df_name,
                     /* string  */ $key)
    {
        // リクエスト構築
        $criteria          = new Cascade_DB_KVS_Criteria;
        $criteria->type    = $criteria->getConstant('TYPE_IS_GET');
        $criteria->df_name = $df_name;
        $criteria->key     = $key;
        // ステートメント構築
        $stmt = new Cascade_DB_KVS_Statement($criteria);
        return $stmt->execute();
    }
    // }}}
    // {{{ mget
    /**
     *  複数の指定KEYの値を取得する
     *
     *  {@link get()}関数と挙動が似ていますが、<br />
     *  複数のKEYを配列型で同時に指定する事ができます。<br />
     *  見つかったデータに関してはCASトークンの情報を同時に取得することができます。
     *
     *  @param   string  スキーマ名
     *  @param   array   KEYリスト
     *  @param   array   CASトークン・リスト
     *  @return  mixed   指定KEYに格納された値とCASトークンを複数取得する。
     *                   存在しない、もしくはエラーの場合はFALSEを返す。
     */
    public /* mixed */
        function mget(/* string */ $df_name,
                      /* array  */ $keys)
    {
        // リクエスト構築
        $criteria          = new Cascade_DB_KVS_Criteria;
        $criteria->type    = $criteria->getConstant('TYPE_IS_MGET');
        $criteria->df_name = $df_name;
        $criteria->key     = $keys;
        // ステートメント構築
        $stmt = new Cascade_DB_KVS_Statement($criteria);
        return $stmt->execute();
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ add
    /**
     *  新規データを追加する
     *
     *  指定のKEYに新規データを追加する。<br/>
     *  既にデータが存在した場合は、処理に失敗しFALSEを返す。
     *
     *  @param   string   データー・フォーマット
     *  @param   string   KEY
     *  @param   mixed   新規追加する値
     *  @param   int      (optional) データ有効期間
     *  @return  boolean  処理結果
     */
    public /* boolean */
        function add(/* string */ $df_name,
                     /* string */ $key,
                     /* mixed  */ $value,
                     /* int    */ $expiration = 0)
    {
        // リクエスト構築
        $criteria          = new Cascade_DB_KVS_Criteria;
        $criteria->type       = $criteria->getConstant('TYPE_IS_ADD');
        $criteria->df_name    = $df_name;
        $criteria->key        = $key;
        $criteria->value      = $value;
        $criteria->expiration = $expiration;
        // ステートメント構築
        $stmt = new Cascade_DB_KVS_Statement($criteria);
        return $stmt->execute();
    }
    // }}}
    // {{{ set
    /**
     *  データを更新する
     *
     *  指定のKEYのデータを更新します。<br/>
     *  データが存在しない場合は、新規にデータを作成します。
     *
     *  @param   string   データー・フォーマット
     *  @param   string   KEY
     *  @param   mixed   新規追加する値
     *  @param   int      (optional) データ有効期間
     *  @return  boolean  処理結果
     */
    public /* boolean */
        function set(/* string */ $df_name,
                     /* string */ $key,
                     /* mixed  */ $value,
                     /* int    */ $expiration = 0)
    {
        // リクエスト構築
        $criteria          = new Cascade_DB_KVS_Criteria;
        $criteria->type       = $criteria->getConstant('TYPE_IS_SET');
        $criteria->df_name    = $df_name;
        $criteria->key        = $key;
        $criteria->value      = $value;
        $criteria->expiration = $expiration;
        // ステートメント構築
        $stmt = new Cascade_DB_KVS_Statement($criteria);
        return $stmt->execute();
    }
    // }}}
    // {{{ replace
    /**
     *  既存データを更新する
     *
     *  指定KEYに既にあるデータを更新します。<br/>
     *  データが存在しない場合は、処理は失敗しFALSEを返します。
     *
     *  @param   string   データー・フォーマット
     *  @param   string   KEY
     *  @param   mixed   新規追加する値
     *  @param   int      (optional) データ有効期間
     *  @return  boolean  処理結果
     */
    public /* boolean */
        function replace(/* string */ $df_name,
                         /* string */ $key,
                         /* mixed  */ $value,
                         /* int    */ $expiration = 0)
    {
        // リクエスト構築
        $criteria          = new Cascade_DB_KVS_Criteria;
        $criteria->type    = $criteria->getConstant('TYPE_IS_REP');
        $criteria->df_name    = $df_name;
        $criteria->key        = $key;
        $criteria->value      = $value;
        $criteria->expiration = $expiration;
        // ステートメント構築
        $stmt = new Cascade_DB_KVS_Statement($criteria);
        return $stmt->execute();
    }
    // }}}
    // {{{ cas
    /**
     *  既存データ状態を確認して更新する
     *
     *  サーバ上の既存データのCASトークンの値と、<br/>
     *  更新処理時に渡されたクライアントのCASトークンを比較して、<br/>
     *  一致すればデータを更新します。一致しない場合は処理は失敗しFALSEを返します。
     *
     *  @param   string   データー・フォーマット
     *  @param   string   CASトークン
     *  @param   string   KEY
     *  @param   mixed   新規追加する値
     *  @param   int      (optional) データ有効期間
     *  @return  boolean  処理結果
     */
    public /* boolean */
        function cas(/* string */ $df_name,
                     /* string */ $cas_token,
                     /* string */ $key,
                     /* mixed  */ $value,
                     /* int    */ $expiration = 0)
    {
        // リクエスト構築
        $criteria          = new Cascade_DB_KVS_Criteria;
        $criteria->type       = $criteria->getConstant('TYPE_IS_CAS');
        $criteria->df_name    = $df_name;
        $criteria->cas_token  = $cas_token;
        $criteria->key        = $key;
        $criteria->value      = $value;
        $criteria->expiration = $expiration;
        // ステートメント構築
        $stmt = new Cascade_DB_KVS_Statement($criteria);
        return $stmt->execute();
    }
    // }}}
    // {{{ delete
    /**
     *  既存データを削除する
     *
     *  指定KEYに保存されているデータを削除する。
     *
     *  @param   string   データー・フォーマット
     *  @param   string   KEY
     *  @return  boolean  処理結果
     */
    public /* boolean */
        function delete(/* string */ $df_name,
                        /* string */ $key)
    {
        // リクエスト構築
        $criteria          = new Cascade_DB_KVS_Criteria;
        $criteria->type    = $criteria->getConstant('TYPE_IS_DEL');
        $criteria->df_name = $df_name;
        $criteria->key     = $key;
        // ステートメント構築
        $stmt = new Cascade_DB_KVS_Statement($criteria);
        return $stmt->execute();
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ increment
    /**
     *  データ値を加算処理する
     *
     *  指定KEYに保存されているデータを加算処理する。
     *   - 加算結果値を結果値として返す
     *   - データが数値型として認識できない場合は0として扱う
     *   - データが存在しない場合は、処理が失敗しFALSEを返す
     *
     *  @param   string  スキーマ名
     *  @param   string  KEY
     *  @param   int     加算する差分値
     *  @return  int     加算後の値
     */
    public /* int */
        function increment(/* string */ $df_name,
                           /* string */ $key,
                           /* int    */ $offset = 1)
    {
        // リクエスト構築
        $criteria          = new Cascade_DB_KVS_Criteria;
        $criteria->type    = $criteria->getConstant('TYPE_IS_INC');
        $criteria->df_name = $df_name;
        $criteria->key     = $key;
        $criteria->offset  = $offset;
        // ステートメント構築
        $stmt = new Cascade_DB_KVS_Statement($criteria);
        return $stmt->execute();
    }
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
     *  @param   string  スキーマ名
     *  @param   string  KEY
     *  @param   int     減算する差分値
     *  @return  int     減算後の値
     */
    public /* int */
        function decrement(/* string */ $df_name,
                           /* string */ $key,
                           /* int    */ $offset = 1)
    {
        // リクエスト構築
        $criteria          = new Cascade_DB_KVS_Criteria;
        $criteria->type    = $criteria->getConstant('TYPE_IS_DEC');
        $criteria->df_name = $df_name;
        $criteria->key     = $key;
        $criteria->offset  = $offset;
        // ステートメント構築
        $stmt = new Cascade_DB_KVS_Statement($criteria);
        return $stmt->execute();
    }
    // }}}
    // {{{ getErrorCode
    /**
     *  エラー番号を取得する
     *
     *  @param   string  スキーマ名
     *  @return  int     エラー番号
     */
    public /* int */
        function getErrorCode(/* string */ $df_name)
    {
        // リクエスト構築
        $criteria          = new Cascade_DB_KVS_Criteria;
        $criteria->type    = $criteria->getConstant('TYPE_IS_ERRNO');
        $criteria->df_name = $df_name;
        // ステートメント構築
        $stmt = new Cascade_DB_KVS_Statement($criteria);
        return $stmt->execute();
    }
    // }}}
    // {{{ getErrorMessage
    /**
     *  エラーメッセージを取得する
     *
     *  @param   string  スキーマ名
     *  @return  string  エラーメッセージ
     */
    public /* string */
        function getErrorMessage(/* string */ $df_name)
    {
        // リクエスト構築
        $criteria          = new Cascade_DB_KVS_Criteria;
        $criteria->type    = $criteria->getConstant('TYPE_IS_ERROR');
        $criteria->df_name = $df_name;
        // ステートメント構築
        $stmt = new Cascade_DB_KVS_Statement($criteria);
        return $stmt->execute();
    }
    // }}}
}