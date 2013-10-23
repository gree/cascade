<?php
/**
 *  EAC.php
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_Driver
 *  @subpackage  KVS
 */

/**
 *  Cascade_Driver_KVS_EAC
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_Driver
 *  @subpackage  KVS
 */
class Cascade_Driver_KVS_EAC
    extends    Cascade_Driver_KVS_Common
    implements Cascade_Driver_KVS
{
    // ----[ Class Constants ]----------------------------------------
    // レスポンス・コード
    // (こだわりは無いけど、Memcachedのコードに揃える)
    const RES_SUCCESS          =  0;
    const RES_FAILURE          =  1;
    const RES_DATA_EXISTS      = 12;
    const RES_NOTFOUND         = 16;
    const RES_BAD_KEY_PROVIDED = 33;

    // ----[ Methods ]------------------------------------------------
    // {{{ is_enable
    /**
     *  ドライバーが利用可能であるかを確認する
     *
     *  @return  boolean  利用可能である場合はTRUEを返す
     */
    public static /* boolean */
        function is_enable(/* boolean */ $notice = TRUE)
    {
        $extension_name   = 'eaccelerator';
        $required_version = '0.9';

        // ドライバの有効確認
        if (extension_loaded($extension_name)) {
            $extension_version = phpversion($extension_name);
            if (version_compare($extension_version, $required_version, '>=')) {
                return TRUE;
            }
        }
        // 警告表示
        $error = 'Disable extension module {name, version} %s (%s<=)';
        $error = sprintf($error, $extension_name, $required_version);
        if ($notice) {
            trigger_error($error, E_USER_NOTICE);
        }
        return FALSE;
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ get
    /**
     *  指定KEYの値を取得する
     *
     *  指定のKEYに格納された値を取得する。<br/>
     *  eAcceleratorはCASトークンを返しません。<br/>
     *  (注意)
     *    NULL値が格納されていた場合は厳密なkeyの存在確認ができないため、<br/>
     *    データが格納されていないものとして扱う。
     *
     *  @param   string  KEY
     *  @param   string  (optional) CASトークン
     *  @return  mixed   指定KEYに格納された値を取得する。
     *                   存在しない、もしくはエラーの場合はFALSEを返す。
     */
    public /** void */
        function get(/** string */  $key,
                     /** scalar */ &$cas_token = NULL)
    {
        $this->s_time_record(__FUNCTION__);
        if (FALSE === ($storage_key = $this->gen_storage_key($key))) {
            $this->errno = self::RES_BAD_KEY_PROVIDED;
            $this->op_err($key, __FUNCTION__);
            return FALSE;
        }
        $value = eaccelerator_get($storage_key);
        if ($value === NULL) {
            $error = 'Not found stored value {namespace, key} %s %s';
            $this->errno = self::RES_NOTFOUND;
            $this->error = sprintf($error, $this->namespace, $key);
            $this->op_err($key, __FUNCTION__);
        }
        $this->op_log($key, __FUNCTION__);
        return $value;
    }
    // }}}
    // {{{ mget
    /**
     *  複数の指定KEYの値を取得する
     *
     *  {@link get()}関数と挙動が似ていますが、<br />
     *  複数のKEYを配列型で同時に指定する事ができます。<br />
     *  eAcceleratorはCASトークンを返しません。
     *
     *  @param   array   KEYリスト
     *  @param   array   (optional) CASトークン・リスト
     *  @return  mixed   指定KEYに格納された値を複数取得する。
     *                   存在しない、もしくはエラーの場合はFALSEを返す。
     */
    public /** void */
        function mget(/** array */  $keys,
                      /** array */ &$cas_tokens = NULL)
    {
        $this->s_time_record(__FUNCTION__);
        $values = array();
        foreach ($keys as $key) {
            if (FALSE === ($storage_key = $this->gen_storage_key($key))) {
                $this->errno = self::RES_BAD_KEY_PROVIDED;
                $this->op_err($key, __FUNCTION__);
                return FALSE;
            }
            $values[$key] = eaccelerator_get($key);
        }
        $this->op_log($key, __FUNCTION__);
        return $values;
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
     *  @param   string   KEY
     *  @param   mixed   新規追加する値
     *  @param   int      (optional) データ有効期間
     *  @return  boolean  処理結果
     */
    public /** void */
        function add(/** string */ $key,
                     /** mixed  */ $value,
                     /** int    */ $expiration = 0)
    {
        $this->s_time_record(__FUNCTION__);
        if (FALSE === ($storage_key = $this->gen_storage_key($key))) {
            $this->errno = self::RES_BAD_KEY_PROVIDED;
            $this->op_err($key, __FUNCTION__);
            return FALSE;
        }
        if (NULL !== eaccelerator_get($storage_key)) {
            $error = 'Already exists key {namespace, key} %s %s';
            $this->errno = self::RES_DATA_EXISTS;
            $this->error = sprintf($error, $this->namespace, $key);
            $this->op_err($key, __FUNCTION__);
            return FALSE;
        }
        if (FALSE === eaccelerator_put($storage_key, $value, $expiration)) {
            $error = 'Faild to store value {namespace, key} %s %s';
            $this->errno = self::RES_FAILURE;
            $this->error = sprintf($error, $this->namespace, $key);
            $this->op_err($key, __FUNCTION__);
            return FALSE;
        }
        $this->op_log($key, __FUNCTION__);
        return TRUE;
    }
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
    public /** void */
        function set(/** string */ $key,
                     /** mixed  */ $value,
                     /** int    */ $expiration = 0)
    {
        $this->s_time_record(__FUNCTION__);
        if (FALSE === ($storage_key = $this->gen_storage_key($key))) {
            $this->errno = self::RES_BAD_KEY_PROVIDED;
            $this->op_err($key, __FUNCTION__);
            return FALSE;
        }
        if (FALSE === eaccelerator_put($storage_key, $value, $expiration)) {
            $error = 'Faild to store value {namespace, key} %s %s';
            $this->errno = self::RES_FAILURE;
            $this->error = sprintf($error, $this->namespace, $key);
            $this->op_err($key, __FUNCTION__);
            return FALSE;
        }
        $this->op_log($key, __FUNCTION__);
        return TRUE;
    }
    // }}}
    // {{{ replace
    /**
     *  Replace the item under an existing key
     *  The operation fails if the key does not exist on the server
     *
     *  @param   $key        The key under which to store the value
     *  @param   $value      The value to store
     *  @param   $expiration (optional) The expiration time, defaults to 0
     */
    public /** void */
        function replace(/** string */ $key,
                         /** mixed  */ $value,
                         /** int    */ $expiration = 0)
    {
        $this->s_time_record(__FUNCTION__);
        if (FALSE === ($storage_key = $this->gen_storage_key($key))) {
            $this->errno = self::RES_BAD_KEY_PROVIDED;
            $this->op_err($key, __FUNCTION__);
            return FALSE;
        }
        if (NULL === eaccelerator_get($storage_key)) {
            $error = 'Not found key {namespace, key} %s %s';
            $this->errno = self::RES_NOTFOUND;
            $this->error = sprintf($error, $this->namespace, $key);
            $this->op_err($key, __FUNCTION__);
            return FALSE;
        }
        if (FALSE === eaccelerator_put($storage_key, $value, $expiration)) {
            $error = 'Faild to store value {namespace, key} %s %s';
            $this->errno = self::RES_FAILURE;
            $this->error = sprintf($error, $this->namespace, $key);
            $this->op_err($key, __FUNCTION__);
            return FALSE;
        }
        $this->op_log($key, __FUNCTION__);
        return TRUE;
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
     *  @param   string   CASトークン
     *  @param   string   KEY
     *  @param   mixed   新規追加する値
     *  @param   int      (optional) データ有効期間
     *  @return  boolean  処理結果
     */
    public /** void */
        function cas(/** string */ $cas_token,
                     /** string */ $key,
                     /** mixed  */ $value,
                     /** int    */ $expiration = 0)
    {
        $ex_msg = '%s Driver Unsupported function :: %s';
        $ex_msg = sprintf($ex_msg, __CLASS__, __FUNCTION__);
        throw new Cascade_Exception_DriverException($ex_msg);
    }
    // }}}
    // {{{ delete
    /**
     *  既存データを削除する
     *
     *  指定KEYに保存されているデータを削除する。
     *
     *  @param   string   KEY
     *  @param   int      (optional) 指定秒数待ってから削除する
     *  @return  boolean  処理結果
     */
    public /** void */
        function delete(/** string */ $key,
                        /** int    */ $time = 0)
    {
        $this->s_time_record(__FUNCTION__);
        if (FALSE === ($storage_key = $this->gen_storage_key($key))) {
            $this->errno = self::RES_BAD_KEY_PROVIDED;
            $this->op_err($key, __FUNCTION__);
            return FALSE;
        }
        if (NULL !== eaccelerator_get($storage_key)) {
            $error = 'Already exists key {namespace, key} %s %s';
            $this->errno = self::RES_NOTFOUND;
            $this->error = sprintf($error, $this->namespace, $key);
            $this->op_err($key, __FUNCTION__);
            return FALSE;
        }
        if (FALSE === eaccelerator_rm($storage_key)) {
            $error = 'Failed to remove stored data {namespace, key} %s %s';
            $this->errno = self::RES_FAILURE;
            $this->error = sprintf($error, $this->namespace, $key);
            $this->op_err($key, __FUNCTION__);
            return FALSE;
        }
        $this->op_log($key, __FUNCTION__);
        return TRUE;
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
     *  @param   string  KEY
     *  @param   int     (optional) 加算する差分値
     *  @return  int     加算後の値
     */
    public /** void */
        function increment(/** string */ $key,
                           /** int    */ $offset = 1)
    {
        $ex_msg = '%s Driver Unsupported function :: %s';
        $ex_msg = sprintf($ex_msg, __CLASS__, __FUNCTION__);
        throw new Cascade_Exception_DriverException($ex_msg);
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
     *  @param   string  データー・フォーマット
     *  @param   string  KEY
     *  @param   int     (optional) 減算する差分値
     *  @return  int     減算後の値
     */
    public /** void */
        function decrement(/** string */ $key,
                           /** int    */ $offset = 1)
    {
        $ex_msg = '%s Driver Unsupported function :: %s';
        $ex_msg = sprintf($ex_msg, __CLASS__, __FUNCTION__);
        throw new Cascade_Exception_DriverException($ex_msg);
    }
    // }}}
};
