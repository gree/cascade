<?php
/**
 *  Config.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_System
 */

/**
 *  Cascade_System_Config
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_System
 */
final class Cascade_System_Config
{
    // ----[ Properties ]---------------------------------------------
    /**
     *  設定ファイル名
     *
     *  スキーマ定義情報を記述するファイル
     *  @var  string
     */
    protected static $file_name = 'cascade.ini.php';

    /**
     *  システム情報が格納されている配列のKEY
     *
     *  @var  string
     */
    protected static $data_idx  = 'system';

    // ----[ Methods ]------------------------------------------------
    // {{{ get
    /**
     *  システム設定情報を取得する
     *
     *  @param   string        セクション
     *  @param   string|array  (optional) 指定の値を取得する場合に指定する
     *  @return  array         設定情報値
     */
    public static /* array */
        function get(/* string */ $section,
                     /* string */ $var_name = NULL)
    {
        // 設定ファイルのPATHを構築
        $file_path = CASCADE_CONFIG_DIR_PATH
            . Cascade::SEPARATOR_DIRECTORY
            . self::$file_name;
        // ドライバを取得する
        $args   = array($file_path, self::$data_idx);
        $driver = Cascade_Driver_Factory::getInstance(Cascade::DRIVER_PHPARRAY, $args);
        // 値を取得する
        return $driver->get($section, $var_name);
    }
    // }}}
};
