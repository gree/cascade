<?php
/**
 *  Cascade_System_Schema
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade
 */

/**
 *  Cascade_System_Schema
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade
 */
final class Cascade_System_Schema
    extends Cascade_Object
{
    // ----[ Class Constants ]----------------------------------------
    /**
     *  namespace    :: Gateway
     */
    const CONFIG_GATEWAY_NS        = 'gateway.namespace';

    /**
     *  class prefix :: Gateway
     */
    const CONFIG_GATEWAY_PREFIX    = 'gateway.prefix';

    /**
     *  class suffix :: Gateway
     */
    const CONFIG_GATEWAY_SUFFIX    = 'gateway.suffix';

    /**
     *  namespace    :: DataFormat
     */
    const CONFIG_DATAFORMAT_NS     = 'dataformat.namespace';

    /**
     *  class prefix :: DataFormat
     */
    const CONFIG_DATAFORMAT_PREFIX = 'dataformat.prefix';

    /**
     *  class suffix :: DataFormat
     */
    const CONFIG_DATAFORMAT_SUFFIX = 'dataformat.suffix';

    // ----[ Class Constants ]----------------------------------------
    /**
     *  名前空間のデフォルト値
     *
     *  スキーマの名前空間が指定されなかった場合に適応される
     */
    const SCHEMA_NS_DEFAULT        = 'default';

    // ----[ Properties ]---------------------------------------------
    /**
     *  設定ファイル名
     *
     *  スキーマ定義情報を記述するファイル
     *  @var  string
     */
    protected static $file_name = 'cascade.ini.php';

    /**
     *  情報が格納されている配列のKEY
     *
     *  設定ファイルにはスキーマ以外の情報も格納されているため<br/>
     *  スキーマ情報が格納されている配列のKEYを指定する
     *
     *  @var  string
     */
    protected static $data_idx  = 'schema';

    // ----[ Methods ]------------------------------------------------
    // {{{ getGatewayClassName
    /**
     *  ゲートウェイ・クラス名を取得する
     *
     *  @param   string  スキーマ名
     *  @return  string  クラス名
     */
    public static /* string */
        function getGatewayClassName(/* string */ $schema_name)
    {
        // 基本情報取得
        $prefix = '';
        $suffix = '';
        list($namespace, $ident) = self::parseSchemaName($schema_name);
        $ns     = self::getSchemaData($namespace, self::CONFIG_GATEWAY_NS);
        $prefix = self::getSchemaData($namespace, self::CONFIG_GATEWAY_PREFIX);
        $suffix = self::getSchemaData($namespace, self::CONFIG_GATEWAY_SUFFIX);
        // 識別子を変換
        $ident = preg_replace('/_(.)/e',   "'_'.strtoupper('\$1')",      ucfirst($ident));
        $ident = preg_replace('/\\\(.)/e', "'\\\\\\'.strtoupper('\$1')", ucfirst($ident));
        // クラス名を構築
        $class_name = $ident;
        if (strlen($ns)) {
            $class_name = $ns . Cascade::SEPARATOR_PHP_NS . $class_name;
        }
        if (strlen($prefix)) {
            $class_name = $prefix . $class_name;
        }
        if (strlen($suffix)) {
            $class_name = $class_name . $suffix;
        }
        return $class_name;
    }
    // }}}
    // {{{ getDataFormatClassName
    /**
     *  ゲートウェイ・クラス名を取得する
     *
     *  @param   string  スキーマ名
     *  @return  string  クラス名
     */
    public static /* string */
        function getDataFormatClassName(/* string */ $schema_name)
    {
        // 基本情報取得
        $prefix = '';
        $suffix = '';
        list($namespace, $ident) = self::parseSchemaName($schema_name);
        $ns     = self::getSchemaData($namespace, self::CONFIG_DATAFORMAT_NS);
        $prefix = self::getSchemaData($namespace, self::CONFIG_DATAFORMAT_PREFIX);
        $suffix = self::getSchemaData($namespace, self::CONFIG_DATAFORMAT_SUFFIX);
        // 識別子を変換
        $ident = preg_replace('/_(.)/e',   "'_'.strtoupper('\$1')",      ucfirst($ident));
        $ident = preg_replace('/\\\(.)/e', "'\\\\\\'.strtoupper('\$1')", ucfirst($ident));
        // クラス名を構築
        $class_name = $ident;
        if (strlen($ns)) {
            $class_name = $ns . Cascade::SEPARATOR_PHP_NS . $class_name;
        }
        if (strlen($prefix)) {
            $class_name = $prefix . $class_name;
        }
        if (strlen($suffix)) {
            $class_name = $class_name . $suffix;
        }
        return $class_name;
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ parseSchemaName
    /**
     *  スキーマ名をパースする
     *
     *  名前をパースして名前空間と識別子を配列型で取得する
     *
     *  @return  array  解析結果
     *    + 名前空間
     *    + 識別子
     */
    public static /* array */
        function parseSchemaName($schema_name)
    {
        // セパレーターの位置を調べる
        $sep = Cascade::SEPARATOR_SCHEMA;
        $pos = strpos($schema_name, $sep);
        // 名前空間, および識別子を取得
        if ($pos === FALSE) {
            $namespace = self::SCHEMA_NS_DEFAULT;
            $ident     = $schema_name;
        } else {
            $namespace = substr($schema_name, 0, $pos);
            $ident     = substr($schema_name, $pos + strlen($sep));
        }
        // 値を確認する
        if (strpos($ident, $sep)  !== FALSE
            || strlen($namespace) < 1
            || strlen($ident)     < 1) {
            $ex_msg = 'Invalid a schame name {schema_name} %s';
            $ex_msg = sprintf($ex_msg, $schema_name);
            throw new Cascade_Exception_Exception($ex_msg);
        }
        // パース結果を返す
        return array($namespace, $ident);
    }
    // }}}
    // {{{ getSchemaData
    /**
     *  スキーマ設定情報を取得する
     *
     *  @param   string  名前空間
     *  @param   string  (optional) 指定の値を取得する場合に指定する
     *  @return  array   スキーマ設定情報値
     */
    protected static /* array */
        function getSchemaData(/* string */ $namespace,
                               /* string */ $ident = NULL)
    {
        // 設定ファイルのPATHを構築
        $file_path = CASCADE_CONFIG_DIR_PATH
            . Cascade::SEPARATOR_DIRECTORY
            . self::$file_name;
        // 設定情報を取得するためのドライバ取得
        $args   = array($file_path, self::$data_idx);
        $driver = Cascade_Driver_Factory::getInstance(Cascade::DRIVER_PHPARRAY, $args);
        // 設定値を取得する
        $data = $driver->get($namespace);
        if (0 < strlen($ident)) {
            foreach (explode(Cascade::SEPARATOR_VAR_NAME_NEST, $ident) as $idx) {
                if (isset($data[$idx]) === FALSE) {
                    return NULL;
                }
                $data = $data[$idx];
            }
        }
        // 結果値を返す
        return $data;
    }
    // }}}
}
