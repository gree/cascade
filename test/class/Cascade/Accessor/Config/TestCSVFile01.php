<?php
/**
 *  TestCSVFile01.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade
 *  @version  $Id:$
 */

/**
 *  Cascade_Accessor_Config_TestCSVFile01
 */
final class Cascade_Accessor_Config_TestCSVFile01
    extends PHPUnit_Framework_TestCase
{
    // ----[ Class Constants ]----------------------------------------
    const SCHEMA_NAME = 'test#Accessor_Config_TestCSVFile01';

    // ----[ Methods ]------------------------------------------------
    // {{{ test_get_01
    public function test_get_01()
    {
        $session = Cascade::getAccessor(self::SCHEMA_NAME);
        $data_01 = $session->get('EMPTY');

        // -----------------------------
        $this->assertNull($data_01);
    }
    // }}}
    // {{{ test_get_02
    public function test_get_02()
    {
        $session = Cascade::getAccessor(self::SCHEMA_NAME);
        $data_01 = $session->get('PRODUCTION');

        // -----------------------------
        $this->assertNull($data_01);
    }
    // }}}
    // {{{ test_get_03
    public function test_get_03()
    {
        $session = Cascade::getAccessor(self::SCHEMA_NAME);
        $data_01 = $session->get(1);

        // -----------------------------
        $this->assertEquals(1,          $data_01['id']);
        $this->assertEquals(1,          $data_01['category']);
        $this->assertEquals('勇者の剣', $data_01['name']);
        $this->assertEquals(1000,       $data_01['str']);
        $this->assertEquals(0,          $data_01['def']);
    }
    // }}}
    // {{{ test_get_all
    public function test_get_all()
    {
        $session = Cascade::getAccessor(self::SCHEMA_NAME);
        $data_01 = $session->getAll();

        // -----------------------------
        $this->assertEquals(1,          $data_01[1]['id']);
        $this->assertEquals(1,          $data_01[1]['category']);
        $this->assertEquals('勇者の剣', $data_01[1]['name']);
        $this->assertEquals(1000,       $data_01[1]['str']);
        $this->assertEquals(0,          $data_01[1]['def']);

        // -----------------------------
        $this->assertEquals(2,          $data_01[2]['id']);
        $this->assertEquals(2,          $data_01[2]['category']);
        $this->assertEquals('勇者の盾', $data_01[2]['name']);
        $this->assertEquals(0,          $data_01[2]['str']);
        $this->assertEquals(1000,       $data_01[2]['def']);
    }
    // }}}
};

/**
 *  データーフォーマット
 */
final class Cascade_Accessor_Config_TestCSVFile01_DataFormat
    extends Cascade_DB_Config_DataFormat
{
    // ----[ Properties ]---------------------------------------------
    // @var string  設定ファイル
    protected $config_path  = CASCADE_CONF_TEST_ROOT;
    // @var string  設定ファイル
    protected $config_file  = 'item.csv';
    // @var int    DB接続ドライバー種別
    protected $driver_type  = self::DRIVER_CSVFILE;
    // @var int    結果のフェッチモード
    protected $fetch_mode   = self::FETCH_MODE_ASSOC;
    // @var array  割り込み処理定義
    protected $interceptors = array();
};
