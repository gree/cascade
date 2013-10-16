<?php
/**
 *  TestIniFile01.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade
 *  @version  $Id:$
 */

/**
 *  Cascade_Accessor_Config_TestIniFile01
 */
final class Cascade_Accessor_Config_TestIniFile01
    extends PHPUnit_Framework_TestCase
{
    // ----[ Class Constants ]----------------------------------------
    const SCHEMA_NAME = 'test#Accessor_Config_TestIniFile01';

    // ----[ Methods ]------------------------------------------------
    // {{{ test_get_all
    public function test_get_all()
    {
        $accessor = Cascade::getAccessor(self::SCHEMA_NAME);
        $data_01  = $accessor->getAll();

        // -----------------------------
        $this->assertEquals('www.example.com',  $data_01['production']['webhost']);
        $this->assertEquals('pdo_mysql',        $data_01['production']['database']['adapter']);
        $this->assertEquals('db.example.com',   $data_01['production']['database']['params']['host']);
        $this->assertEquals('dbuser',           $data_01['production']['database']['params']['username']);
        $this->assertEquals('secret',           $data_01['production']['database']['params']['password']);
        $this->assertEquals('dbname',           $data_01['production']['database']['params']['dbname']);
        $this->assertEquals('www.example.com',  $data_01['staging']['webhost']);
        $this->assertEquals('pdo_mysql',        $data_01['staging']['database']['adapter']);
        $this->assertEquals('dev.example.com',  $data_01['staging']['database']['params']['host']);
        $this->assertEquals('devuser',          $data_01['staging']['database']['params']['username']);
        $this->assertEquals('devsecret',        $data_01['staging']['database']['params']['password']);
        $this->assertEquals('dbname',           $data_01['staging']['database']['params']['dbname']);
    }
    // }}}
    // {{{ test_get_01
    public function test_get_01()
    {
        $accessor = Cascade::getAccessor(self::SCHEMA_NAME);
        $data_01  = $accessor->get('EMPTY');

        // -----------------------------
        $this->assertNull($data_01);
    }
    // }}}
    // {{{ test_get_02
    public function test_get_02()
    {
        $accessor = Cascade::getAccessor(self::SCHEMA_NAME);
        $data_01 = $accessor->get('PRODUCTION');

        // -----------------------------
        $this->assertNull($data_01);
    }
    // }}}
    // {{{ test_get_03
    public function test_get_03()
    {
        $accessor = Cascade::getAccessor(self::SCHEMA_NAME);
        $data_01 = $accessor->get('production');

        // -----------------------------
        $this->assertEquals('www.example.com', $data_01['webhost']);
        $this->assertEquals('pdo_mysql',       $data_01['database']['adapter']);
        $this->assertEquals('db.example.com',  $data_01['database']['params']['host']);
        $this->assertEquals('dbuser',          $data_01['database']['params']['username']);
        $this->assertEquals('secret',          $data_01['database']['params']['password']);
        $this->assertEquals('dbname',          $data_01['database']['params']['dbname']);
    }
    // }}}
    // {{{ test_get_04
    public function test_get_04()
    {
        $accessor = Cascade::getAccessor(self::SCHEMA_NAME);
        $data_01  = $accessor->get('staging');

        // -----------------------------
        $this->assertEquals('www.example.com', $data_01['webhost']);
        $this->assertEquals('pdo_mysql',       $data_01['database']['adapter']);
        $this->assertEquals('dev.example.com', $data_01['database']['params']['host']);
        $this->assertEquals('devuser',         $data_01['database']['params']['username']);
        $this->assertEquals('devsecret' ,      $data_01['database']['params']['password']);
        $this->assertEquals('dbname',          $data_01['database']['params']['dbname']);
    }
    // }}}
    // {{{ test_count_01
    public function test_count_01()
    {
        $accessor = Cascade::getAccessor(self::SCHEMA_NAME);
        $data_01  = $accessor->count('staging');

        // -----------------------------
        $this->assertEquals(2, $data_01);
    }
    // }}}
};

/**
 *  データーフォーマット
 */
final class Cascade_Accessor_Config_TestIniFile01_DataFormat
    extends Cascade_DB_Config_DataFormat
{
    // ----[ Properties ]---------------------------------------------
    // @var string  設定ファイル
    protected $config_path  = CASCADE_CONF_TEST_ROOT;
    // @var string  設定ファイル
    protected $config_file  = 'server.ini';
    // @var int    DB接続ドライバー種別
    protected $driver_type  = self::DRIVER_INIFILE;
    // @var int    結果のフェッチモード
    protected $fetch_mode   = self::FETCH_MODE_ASSOC;
    // @var array  割り込み処理定義
    protected $interceptors = array();
};
