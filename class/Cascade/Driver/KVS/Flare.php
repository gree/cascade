<?php
/**
 *  Flare.php
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_Driver
 *  @subpackage  KVS
 */
if (Cascade_Driver_KVS_Libmemcached::is_enable($notice = FALSE)) {
    /**
     *  Cascade_Driver_KVS_Flare
     *
     *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
     *  @package     Cascade_Driver
     *  @subpackage  KVS
     */
    class Cascade_Driver_KVS_Flare
        extends Cascade_Driver_KVS_Libmemcached
    {
        // {{{ get_server_list
        /**
         *  サーバリストを取得
         *
         *  クライアントに登録するサーバリストを取得する。
         *
         *  @return  array  サーバーリスト
         */
        protected /* array */
            function get_server_list(/* void */)
        {
            $pos_list = array(
                getmypid() % count($this->dsn['extra']['hostspec']),
                time()     % count($this->dsn['extra']['hostspec']),
            );
            $server_list = array();
            foreach ($pos_list as $pos) {
                $hostspec = $this->dsn['extra']['hostspec'][$pos];
                list($host, $port) = (strpos($hostspec, ':') !== FALSE)
                    ? explode(':', $hostspec)
                    : array($hostspec, NULL);
                $server_list[] = array($host, $port);
            }
            return $server_list;
        }
        // }}}
        // {{{ get_client
        /**
         *  サーバリストを取得
         *
         *  クライアントに登録するサーバリストを取得する。
         *
         *  @return  array  サーバーリスト
         */
        protected /* void */
            function get_client(/* void */)
        {
            static $instances = array();

            // クライアントを取得
            $token = $this->dsn['string'];
            if (isset($instances[$token]) === FALSE) {
                $client = class_exists('Memcached', $autoload = FALSE)
                    ? new Memcached($token)
                    : new Libmemcached($token);
                $client->behavior_set(self::BEHAVIOR_SERVER_FAILURE_LIMIT, 2);
                $instances[$token] = $client;
            }
            $client = $instances[$token];

            // サーバ登録処理
            if (count($client->server_list()) < 1) {
                foreach ($this->get_server_list() as $server) {
                    list($host, $port) = $server;
                    $client->server_list_append($host, $port);
                }
                $client->server_push();
            }

            // サーバリストが登録されたか確認する
            if (count($client->server_list()) < 1) {
                $error = 'Not found server list {dsn} %s';
                $this->error_code    = self::RES_BAD_KEY_PROVIDED;
                $this->error_message = sprintf($error, $this->dsn['string']);
                return FALSE;
            }

            // クライアントを返す
            return $client;
        }
        // }}}
    }
} else {
    /**
     *  Cascade_Driver_KVS_Flare
     *
     *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
     *  @package     Cascade_Driver
     *  @subpackage  KVS
     */
    class Cascade_Driver_KVS_Flare
        extends Cascade_Driver_KVS_Memcached
    {
        // {{{ get_server_list
        /**
         *  サーバリストを取得
         *
         *  クライアントに登録するサーバリストを取得する。
         *
         *  @return  array  サーバーリスト
         */
        protected /* array */
            function get_server_list(/* void */)
        {
            $pos_list = array(
                getmypid() % count($this->dsn['extra']['hostspec']),
                time()     % count($this->dsn['extra']['hostspec']),
            );
            $server_list = array();
            foreach ($pos_list as $pos) {
                $hostspec = $this->dsn['extra']['hostspec'][$pos];
                list($host, $port) = (strpos($hostspec, ':') !== FALSE)
                    ? explode(':', $hostspec)
                    : array($hostspec, NULL);
                $server_list[] = array($host, $port);
            }
            return $server_list;
        }
        // }}}
        // {{{ get_client
        /**
         *  サーバリストを取得
         *
         *  クライアントに登録するサーバリストを取得する。
         *
         *  @return  array  サーバーリスト
         */
        protected /* void */
            function get_client(/* void */)
        {
            static $instances = array();

            // クライアントを取得
            $token = $this->dsn['string'].date('Ymd_H');
            if (isset($instances[$token]) === FALSE) {
                $client = new Memcached($token);
                $client->setOption(self::BEHAVIOR_SERVER_FAILURE_LIMIT, 2);
                $extension_version = phpversion('memcached');
                if (version_compare($extension_version, '2.0.1', '>=')) {
                    $client->setOption(Memcached::OPT_TCP_KEEPALIVE, 1);
                }
                if ($this->compressed) {
                    $client->setOption(self::OPT_COMPRESSION, 1);
                }
                $instances[$token] = $client;
            }
            $client = $instances[$token];

            // サーバ登録処理
            if (count($client->getServerList()) < 1) {
                $client->addServers($this->get_server_list());
            }

            // サーバリストが登録されたか確認する
            if (count($client->getServerList()) < 1) {
                $error = 'Not found server list {dsn} %s';
                $this->error_code    = self::RES_BAD_KEY_PROVIDED;
                $this->error_message = sprintf($error, $this->dsn['string']);
                return FALSE;
            }

            // クライアントを返す
            return $client;
        }
        // }}}
    }
}

