<?php
/**
 *  IniFile.php
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_Driver
 *  @subpackage  Config
 */

/**
 *  IniFileによる設定データ管理
 *
 *  @author      Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package     Cascade_Driver
 *  @subpackage  Config
 */
final class    Cascade_Driver_Config_IniFile
    extends    Cascade_Driver_Config_Common
    implements Cascade_Driver_Config
{
    // ----[ Properties ]---------------------------------------------
    // {{{ ERROR
    /**
     *  エラー・コード
     *  @var  int
     */
    protected $error_code     = 0;

    /**
     *  エラー・メッセージ
     *  @var  string
     */
    protected $error_message  = NULL;
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ is_enable
    /**
     *  利用可能なドライバかを確認する
     *
     *  PHPの拡張モジュールの読み込み状態や、<br />
     *  バージョン情報を考慮しドライバの有効の有無を判断する
     *
     *  @return  boolean  TRUE:利用可能ドライバ
     */
    public static /** boolean */
        function is_enable(/** void */)
    {
        return TRUE;
    }
    // }}}
    // {{{ get_version
    /**
     *  ドライバーのバージョン情報を取得する
     *
     *  @return  int  バージョン情報
     */
    public static /* string */
        function get_version(/* void */)
    {
        return $version = '0.1';
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ config_file_load
    /**
     *  PHP ARRAY ファイルの読み込み処理
     *
     *  @param   string   設定ファイルPATH
     *  @param   string   (optional) 配列KEYを指定する場合
     *  @return  array    読み込み結果データ
     */
    protected /* array */
        function config_file_load(/* string */ $file_path,
                                  /* string */ $index = NULL)
    {
        // 設定ファイルの存在確認
        if (file_exists($file_path) === FALSE) {
            $error = 'Not found config-file {file_path} %s';
            $this->error_code    = -1;
            $this->error_message = sprintf($error, $file_path);
            return FALSE;
        }
        // ファイル読み込み
        $loaded_file_data = parse_ini_file($file_path, $process_sections = TRUE);
        if ($loaded_file_data === FALSE) {
            $error = 'Failed to load config-file {file_path} %s';
            $this->error_code    = -1;
            $this->error_message = sprintf($error, $file_path);
        }
        // データ構築
        $file_data = array();
        foreach ($loaded_file_data as $section => $data) {
            $tmp = explode(Cascade::SEPARATOR_SECTION_NAME, $section);
            $section = trim($tmp[0]);
            switch (count($tmp)) {
            case 1:
                $file_data[$section] = $data;
                break;
            case 2:
                $parent_section = trim($tmp[1]);
                $file_data[$section][self::EXTEND_INFO_KEY] = $parent_section;
                $file_data[$section] = array_merge($file_data[$section], $data);
                break;
            default:
                $error = 'Could not extend multiple sections {file, section} %s %s';
                $this->error_code    = -1;
                $this->error_message = sprintf($error, $file_path, $section);
                return FALSE;
            }
        }
        return $file_data;
    }
    // }}}
};