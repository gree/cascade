<?php
/**
 *  DSN.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_System
 */

/**
 *  Cascade_System_DSN
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_System
 */
final class Cascade_System_DSN
{
    // ----[ Class Constants ]----------------------------------------
    /**
     *  DSN文字列の正規表現
     *
     *  DSN文字列として認識可能な表現を定義
     *  <code>
     *    phptype://username:password@hostspec/database
     *    phptype(dbsyntax)://username:password@category/identifier
     *  </code>
     */
    const PATTERN_DSN_TOKEN = '/^(\w+|\w+\(\w+\)):\/\/([.:\w]+)\/(\w+){0,1}$/';

    /**
     *  設定ファイルのセクション名
     */
    const CONFIG_SECTION    = 'dsn-config-gree';

    /**
     *  標準データーベース
     *
     *  DSN定義でdbsyntaxが省略されたときに設定する値
     */
    const DEFAULT_DBSYNTAX  = 'mysql';

    // ----[ Methods ]------------------------------------------------
    // {{{ parse
    /**
     *  DSNのパース処理
     *
     *  パース処理結果に、ホスト情報を追加して結果値を返す。
     *
     *  @param   string  Database-Source-Name
     *  @return  array   パース結果値
     */
    public static /* array */
        function parse(/* string */ $dsn)
    {
        if (preg_match(self::PATTERN_DSN_TOKEN, $dsn, $match) === 0) {
            $ex_msg  = 'Unsupported Database-Source-Name {dsn} %s';
            $ex_msg  = sprintf($ex_msg, $dsn);
            throw new Cascade_Exception_SystemException($ex_msg);
        }
        // DSNのパース処理
        $parsed = self::parse_common($dsn);
        if ($parsed['extra']['identifier'] !== NULL) {
            // サーバー情報を取得
            $server_info = self::get_server_info(
                $parsed['dbsyntax'],
                $parsed['extra']['identifier'],
                $parsed['extra']['category']
            );
            // DSNのパース結果と、サーバー情報をマージする
            $parsed = self::append($parsed, $server_info);
        }
        return $parsed;
    }
    // }}}
    // {{{ parse_common
    /**
     *  DSNのパース処理
     *
     *  @param   string  Database-Source-Name
     *  @return  array   パース結果値
     *  + type     : DSN分類
     *  + dbsyntax : データベース種別 (memcache, flare etc.)
     *  + username : ログイン・ユーザ
     *  + password : ログイン・パスワード
     *  + host     : 接続ホスト
     *  + port     : 接続ポート番号
     *  + database : 選択データーベース名
     *  + extra
     *     + hostspec   : ホスト記述 (hostname[:port])
     *     + identifier : 識別子(dbsyntaxと一緒に使う)
     *     + category   : サーバー分類 (master, slave, standby etc.)
     */
    protected static /* array */
        function parse_common(/* string */ $dsn)
    {
        // パース結果の初期化
        $parsed = array(
            'string'   => $dsn,
            'type'     => NULL,
            'dbsyntax' => NULL,
            'user'     => NULL,
            'pass'     => NULL,
            'host'     => NULL,
            'port'     => NULL,
            'database' => NULL,
            'extra'    => array(
                'identifier' => NULL,
                'category'   => NULL,
                'hostspec'   => array(),
            ),
        );
        // Find phptype and dbsyntax
        $pos = strpos($dsn, ':');
        $str = substr($dsn, 0, $pos);
        $dsn = substr($dsn, $pos + 3);
        // $str => phptype(dbsyntax)
        if (preg_match('|^(.+?)\((.*?)\)$|', $str, $tmp) === 0) {
            $parsed['type']     = $str;
            $parsed['dbsyntax'] = self::DEFAULT_DBSYNTAX;
        } else {
            $parsed['type']     =  $tmp[1];
            $parsed['dbsyntax'] = !$tmp[2] ? $tmp[1] : $tmp[2];
        }
        // Get (if found): username and password
        // $dsn => username:password@hostspec/database
        if (FALSE !== ($pos = strrpos($dsn,'@'))) {
            $str = substr($dsn, 0, $pos);
            $dsn = substr($dsn, $pos + 1);
            if (FALSE !== ($pos = strpos($str, ':'))) {
                $parsed['user'] = rawurldecode(substr($str, 0, $pos));
                $parsed['pass'] = rawurldecode(substr($str, $pos + 1));
            } else {
                $parsed['user'] = rawurldecode($str);
            }
        }
        // Get hostspec
        // $dsn => database
        list($tmp, $dsn) = explode('/', $dsn, 2);
        if (preg_match('/^[0-9.:]+$/', $tmp) === 0) {
            $parsed['extra']['category']   = rawurldecode($tmp);
            $parsed['extra']['identifier'] = $dsn ? rawurldecode($dsn) : 'default';
        } else {
            $hostspec                    = $tmp;
            $parsed['extra']['hostspec'] = array($hostspec);
            list(
                $parsed['host'],
                $parsed['port']
            ) = (strpos($hostspec, ':') !== FALSE)
                ? explode(':', $hostspec)
                : array($hostspec, NULL);
            $parsed['database'] = $dsn ? rawurldecode($dsn) : 'default';
        }
        return $parsed;
    }
    // }}}
    // {{{ get_server_info
    /**
     *  条件に一致するサーバ情報を取得する
     *
     *  @param   string  データベース種別
     *  @param   string  接続ホスト識別子
     *  @param   string  接続ホスト種別
     *  @return  array   条件にマッチするサーバー情報
     */
    protected static /* array */
        function get_server_info(/* string */ $dbsyntax,
                                 /* string */ $identifier,
                                 /* string */ $category)
    {
        // 設定情報を取得する
        static $cached_config = array();
        if (isset($cached_config[$dbsyntax]) === FALSE) {
            $tmp = Cascade_System_Config::get(self::CONFIG_SECTION);
            $tmp = isset($tmp[$dbsyntax]) ? $tmp[$dbsyntax] : $tmp['default'];
            include($tmp['path']);
            $cached_config[$dbsyntax] = $$tmp['var'];
        }
        $config = $cached_config[$dbsyntax];
        // 設定ファイルに定義されているかを確認
        if (isset($config[$identifier]) === FALSE) {
            $ex_msg  = 'Undefined DB identifier {identifier} %s';
            $ex_msg  = sprintf($ex_msg, $identifier);
            trigger_error($ex_msg, E_USER_ERROR);
            throw new Cascade_Exception_SystemException($ex_msg);
        }
        // defaultが設定されているか確認
        if (isset($config['default']) === FALSE) {
            $ex_msg  = 'Undefined DB \'default\' identifier';
            trigger_error($ex_msg, E_USER_ERROR);
            throw new Cascade_Exception_SystemException($ex_msg);
        }
        $specified   = $config[$identifier];
        $server_info = $config['default'];
        foreach ($specified as $key => $val) {
            $server_info[$key] = $val;
        }
        return $server_info;
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ append
    /**
     *  DSNのパース結果と、サーバー情報をマージする
     *
     *  @param   array  DSNパース結果
     *  @param   array  サーバー情報
     *  @return  array  マージ結果
     */
    protected static /* array */
        function append($parsed, $server_info)
    {
        // ホスト設定
        if (empty($server_info[$parsed['extra']['category']])) {
            $ex_msg  = 'Not found server list {identifier, category} %s %s';
            $ex_msg  = sprintf($ex_msg, $parsed['extra']['identifier'], $parsed['extra']['category']);
            trigger_error($ex_msg, E_USER_ERROR);
            throw new Cascade_Exception_SystemException($ex_msg);
        }
        $parsed['extra']['hostspec'] = $server_info[$parsed['extra']['category']];
        if (is_array($parsed['extra']['hostspec']) === FALSE) {
            $parsed['extra']['hostspec'] = array($parsed['extra']['hostspec']);
        }
        // データーベース種別で追加処理がある場合は処理を実行
        $func_name = 'append_for_'.$parsed['dbsyntax'];
        if (is_callable(array(__CLASS__, $func_name))) {
            $parsed = self::$func_name($parsed, $server_info);
        }
        return $parsed;
    }
    // }}}
    // {{{ append_for_mysql
    /**
     *  DSNのパース結果と、サーバー情報をマージする
     *
     *  @param   array  DSNパース結果
     *  @param   array  サーバー情報
     *  @return  array  マージ結果
     */
    protected static /* array */
        function append_for_mysql($parsed, $server_info)
    {
        // ユーザ名設定
        if ($parsed['user'] === NULL) {
            $key_exists = array_key_exists('ro_user', $server_info);
            $is_master  = strtolower($parsed['extra']['category']) === 'master';
            $parsed['user'] = ($key_exists && $is_master === FALSE)
                ? $server_info['ro_user']
                : $server_info['user'];
        }
        // パスワード設定
        if ($parsed['pass'] === NULL) {
            $key_exists = array_key_exists('ro_pass', $server_info);
            $is_master  = strtolower($parsed['extra']['category']) === 'master';
            $parsed['pass'] = ($key_exists && $is_master === FALSE)
                ? $server_info['ro_pass']
                : $server_info['pass'];
        }
        // データーベースを選択
        if (!isset($server_info['db'])) {
            $parsed['database'] = null;
        } else {
            $parsed['database'] = $server_info['db'];
        }
        // 結果値を返す
        return $parsed;
    }
    // }}}
};