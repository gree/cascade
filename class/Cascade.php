<?php
/**
 *  Cascade.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade
 */

/**
 *  Cascade
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade
 */
final class Cascade
{
    // ----[ Methods ]------------------------------------------------
    // {{{ getAccessor
    /**
     *  アクセサを取得する
     *
     *  @param   string                   識別子
     *  @return  Cascade_Proxy_DBGateway  ゲートウェイ
     */
    public static /* Cascade_Proxy_DBGateway */
        function getAccessor(/* string */ $schema_name)
    {
        return Cascade_Proxy_Gateway::getInstance($schema_name);
    }
    // }}}
    // {{{ getDataFormat
    /**
     *  データフォーマットを取得する
     *
     *  @param   string                 識別子
     *  @return  Cascade_DB_DataFormat  データーフォーマット
     */
    public static /* Cascade_DB_DataFormat */
        function getDataFormat(/* string */ $schema_name)
    {
        return Cascade_DB_DataFormat::getInstance($schema_name);
    }
    // }}}
    // {{{ registerAutoload
    /**
     *  PHPファイルの動的呼び出しスタックに登録する
     *
     *  @param   $dir_path   ファイルが格納されているディレクトリPATH
     *  @param   $prefix     (optional) クラス名のprefix
     *  @param   $file_ext   (optional) ファイル拡張子
     */
    public static /* void */
        function registerAutoload(/* string */ $dir_path,
                                  /* string */ $prefix   = '',
                                  /* string */ $file_ext = '.php')
    {
        Cascade_System_ClassLoader::register($dir_path, $prefix, $file_ext);
    }
    // }}}
    // {{{ registerDynamicQueryHandler
    /**
     *  データフォーマットの動的クエリー取得部分にハンドラーを定義する
     *
     *  @param  callback  コールバック関数
     */
    public static /* void */
        function registerDynamicQueryHandler(/* callback */ $callback)
    {
        Cascade_DB_SQL_DataFormat::setExtraStaticProperty(
            Cascade_DB_DataFormat::EXTRA_STATIC_PROP_DQ_CALLBACK, $callback);
    }
    // }}}
    // ----[ Class Constants ]----------------------------------------
    // {{{ SESSION
    /**
     *  SQL操作のファサード
     */
    const SESSION_SQL              = 0x01;

    /**
     *  Key-Value Store操作のファサード
     */
    const SESSION_KVS              = 0x02;

    /**
     *  設定ファイル操作のファサード
     */
    const SESSION_CONFIG           = 0x03;
    // }}}
    // ----[ Class Constants ]----------------------------------------
    // {{{ DRIVER
    /**
     *  MySQLi 拡張関数を使用する
     *
     *  詳細 : http://php.net/manual/book.mysqli.php
     */
    const DRIVER_MYSQLI            = 0x01;

    /**
     *  eAccelerator 拡張関数を使用する
     *
     *  詳細 : http://eaccelerator.net
     */
    const DRIVER_EAC               = 0x11;

    /**
     *  APC 拡張関数を使用する
     *
     *  詳細 : http://pecl.php.net/package/apc
     */
    const DRIVER_APC               = 0x12;

    /**
     *  Memcached 拡張関数を使用する
     *
     *  詳細 : http://pecl.php.net/package/memcached
     */
    const DRIVER_MEMCACHED         = 0x13;

    /**
     *  Libmemcached 拡張関数を使用する
     *
     *  詳細 : http://github.com/kajidai/php-libmemcached
     *  (グリーでの歴史的経緯をふまえて)
     */
    const DRIVER_LIBMEMCACHED      = 0x14;

    /**
     *  Flare (key-value store) を使用する
     *
     *  詳細 : http://labs.gree.jp/Top/OpenSource/Flare.html
     *  プロトコルはmemcachedと同じ仕様である
     */
    const DRIVER_FLARE             = 0x15;

    /**
     *  Squall (key-value store) を利用する
     *
     *  詳細 : オープンソースでの公開は未定
     *  プロトコルはprotobuf互換
     */
    const DRIVER_SQUALL            = 0x16;

    /**
     *  PHP Array 形式を利用する
     *
     *  設定ファイル PHP Array 形式を利用する
     */
    const DRIVER_PHPARRAY          = 0x21;

    /**
     *  Ini file 形式を利用する
     *
     *  設定ファイル PHP Ini-File 形式を利用する
     *  (セクション値の継承ができるように拡張されている)
     */
    const DRIVER_INIFILE           = 0x22;

    /**
     *  CSV file 形式を利用する
     *
     *  設定ファイル CSV 形式を利用する
     *    - 1行目はフィールド名
     *    - 2行目以降データ
     *    - 1カラム目はINDEX-KEY
     */
    const DRIVER_CSVFILE           = 0x23;

    /**
     *  ファイルへのログ記録を利用する
     */
    const DRIVER_LOG_FILE          = 0x31;
    // }}}
    // ----[ Class Constants ]----------------------------------------
    // {{{ SEPARATOR
    /**
     *  PHP 名前空間の区切り文字
     */
    const SEPARATOR_PHP_NS         = '\\';

    /**
     *  スキーマ名の名前空間と識別子を分ける区切り文字
     */
    const SEPARATOR_SCHEMA         = '#';

    /**
     *  ディレクトリ区切りを表現する文字
     */
    const SEPARATOR_DIRECTORY      = '/';

    /**
     *  ネスト・レベルの区切り文字
     */
    const SEPARATOR_VAR_NAME_NEST  = '.';

    /**
     *  設定ファイルのセクション区切り文字
     */
    const SEPARATOR_SECTION_NAME   = ':';
    // }}}
};

// ----[ Global Constants ]-------------------------------------------
// ライブラリのソース・ディレクトリのROOT PATH
if (!defined('CASCADE_SRC_ROOT')) {
      define('CASCADE_SRC_ROOT', dirname(__FILE__));
}

// 設定ファイルのインデックス : システム設定
if (!defined('CASCADE_CONFIG_INDEX_SYSTEM')) {
      define('CASCADE_CONFIG_INDEX_SYSTEM', 'system');
}

// 設定ファイルのインデックス : スキーマ設定
if (!defined('CASCADE_CONFIG_INDEX_SCHEMA')) {
      define('CASCADE_CONFIG_INDEX_SCHEMA', 'schema');
}

// 設定ファイルのディレクトリPATH
if (!defined('CASCADE_CONFIG_DIR_PATH')) {
      define('CASCADE_CONFIG_DIR_PATH',     '/etc/cascade');
}

// ひな形のディレクトリPATH
if (!defined('CASCADE_SKEL_DIR_PATH')) {
      define('CASCADE_SKEL_DIR_PATH',       '/etc/cascade/skel');
}

// キャッシュ機能をOFFにする
if (!defined('CASCADE_DISABLE_CACHE')) {
      define('CASCADE_DISABLE_CACHE', 0);
}

// ----[ Autoload ]---------------------------------------------------
// Autoloaderの設定
require_once CASCADE_SRC_ROOT.'/Cascade/System/ClassLoader.php';
spl_autoload_register('Cascade_System_ClassLoader::load');
