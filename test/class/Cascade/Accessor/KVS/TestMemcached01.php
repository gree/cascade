<?php
/**
 *  TestMemcached01.php5
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade
 *  @version  $Id:$
 */

/**
 *  Cascade_Accessor_KVS_TestMemcached01
 */
final class Cascade_Accessor_KVS_TestMemcached01
    extends PHPUnit_Framework_TestCase
{
    // ----[ Class Constants ]----------------------------------------
    const SCHEMA_NAME = 'test#Accessor_KVS_TestMemcached01';

    // ----[ Methods ]------------------------------------------------
    // {{{ test_mget_01
    public function test_mget_01()
    {
        $data_list = array(
            1 => 'TEST_01',
            2 => 'TEST_02',
            3 => 'TEST_03',
            4 => 'TEST_04',
        );
        $accessor = Cascade::getAccessor(self::SCHEMA_NAME);
        foreach (array_keys($data_list) as $key) {
            $accessor->delete($key);
        }

        // -----------------------------
        foreach ($data_list as $key => $val) {
            $accessor->add($key, $val);
        }
        $fetch_keys = array(1, 3, 5);
        $fetch_data_list = $accessor->mget($fetch_keys);

        // -----------------------------
        $this->assertEquals(2,         count($fetch_data_list[0]));
        $this->assertEquals('TEST_01', $fetch_data_list[0][1]);
        $this->assertEquals('TEST_03', $fetch_data_list[0][3]);
    }
    // }}}
    // {{{ test_add_01
    public function test_add_01()
    {
        $key      = 564;
        $value    = 100000;
        $accessor = Cascade::getAccessor(self::SCHEMA_NAME);
        $accessor->delete($key);

        // -----------------------------
        $data_01 = $accessor->add($key, $value);
        $data_02 = $accessor->add($key, $value);

        // -----------------------------
        $this->assertTrue($data_01);
        $this->assertFalse($data_02);
    }
    // }}}
    // {{{ test_set_01
    public function test_set_01()
    {
        $key     = 564;
        $value   = 10000;
        $accessor = Cascade::getAccessor(self::SCHEMA_NAME);
        $accessor->delete($key);

        // -----------------------------
        $data_01 = $accessor->set($key, $value);
        $actual_key = $accessor->get($key);
        $data_02 = array_shift($actual_key);

        // -----------------------------
        $this->assertTrue($data_01);
        $this->assertEquals($value, $data_02);
    }
    // }}}
    // {{{ test_set_02
    public function test_set_02()
    {
        $key     = 564;
        $value   = 10000;
        $accessor = Cascade::getAccessor(self::SCHEMA_NAME);
        $accessor->delete($key);

        // -----------------------------
        $data_01 = $accessor->add($key, $value);
        $data_02 = $accessor->set($key, $value = 20000);
        $actual_key = $accessor->get($key);
        $data_03 = array_shift($actual_key);

        // -----------------------------
        $this->assertTrue($data_01);
        $this->assertTrue($data_02);
        $this->assertEquals($value, $data_03);
    }
    // }}}
    // {{{ test_replace_01
    public function test_replace_01()
    {
        $key     = 564;
        $value   = 10000;
        $accessor = Cascade::getAccessor(self::SCHEMA_NAME);
        $accessor->delete($key);

        // -----------------------------
        $data_01 = $accessor->replace($key, $value);
        $actual_key = $accessor->get($key);
        $data_02 = array_shift($actual_key);

        // -----------------------------
        $this->assertFalse($data_01);
        $this->assertFalse($data_02);
    }
    // }}}
    // {{{ test_replace_02
    public function test_replace_02()
    {
        $key     = 564;
        $value   = 10000;
        $accessor = Cascade::getAccessor(self::SCHEMA_NAME);
        $accessor->delete($key);

        // -----------------------------
        $data_01 = $accessor->add($key, $value);
        $data_02 = $accessor->replace($key, $value = 20000);
        $actual_key = $accessor->get($key);
        $data_03 = array_shift($actual_key);

        // -----------------------------
        $this->assertTrue($data_01);
        $this->assertTrue($data_02);
        $this->assertEquals($value, $data_03);
    }
    // }}}
    // {{{ test_cas_01
    public function test_cas_01()
    {
        $key      = 564;
        $value_01 = 10000;
        $value_02 = 20000;
        $accessor  = Cascade::getAccessor(self::SCHEMA_NAME);
        $accessor->delete($key);

        // -----------------------------
        $data_01 = $accessor->add($key, $value_01);
        list(
            $data_02,
            $cas_token
        ) = $accessor->get($key);
        $data_03 = $accessor->cas($cas_token, $key, $value_02);
        $actual_key = $accessor->get($key);
        $data_04 = array_shift($actual_key);

        // -----------------------------
        $this->assertTrue($data_01);
        $this->assertTrue($data_03);
        $this->assertEquals($value_01, $data_02);
        $this->assertEquals($value_02, $data_04);
    }
    // }}}
    // {{{ test_cas_02
    public function test_cas_02()
    {
        $key     = 564;
        $value   = 10000;
        $accessor = Cascade::getAccessor(self::SCHEMA_NAME);
        $accessor->delete($key);

        // -----------------------------
        list(
            $data_01,
            $cas_token
        ) = $accessor->get($key);
        $data_02 = $accessor->cas($cas_token, $key, $value);
        $data_03 = $accessor->add($key, $value);

        // -----------------------------
        $this->assertFalse($data_01);
        $this->assertFalse($data_02);
        $this->assertTrue($data_03);
    }
    // }}}
    // {{{ test_cas_03
    public function test_cas_03()
    {
        $key      = 564;
        $value_01 = 10000;
        $value_02 = 20000;
        $value_03 = 30000;
        $accessor  = Cascade::getAccessor(self::SCHEMA_NAME);
        $accessor->delete($key);

        // -----------------------------
        $data_01 = $accessor->add($key, $value_01);
        list(
            $data_02,
            $cas_token
        ) = $accessor->get($key);
        $data_03 = $accessor->set($key, $value_02);
        $actual_key = $accessor->get($key);
        $data_04 = array_shift($actual_key);
        $data_05 = $accessor->cas($cas_token, $key, $value_03);
        $actual_key = $accessor->get($key);
        $data_06 = array_shift($actual_key);

        // -----------------------------
        $this->assertTrue($data_01);
        $this->assertTrue($data_03);
        $this->assertFalse($data_05);
        $this->assertEquals($value_01, $data_02);
        $this->assertEquals($value_02, $data_04);
        $this->assertEquals($value_02, $data_06);
    }
    // }}}
    // {{{ test_cas_04
    public function test_cas_04()
    {
        $key      = 564;
        $value_01 = 10000;
        $value_02 = 20000;
        $accessor = Cascade::getAccessor(self::SCHEMA_NAME);
        $accessor->delete($key);

        // -----------------------------
        $data_01 = $accessor->add($key, $value_01);
        list(
            $data_02,
            $cas_token
        ) = $accessor->get($key);
        $accessor->delete($key);
        $data_03 = $accessor->cas($cas_token, $key, $value_02);
        $actual_key = $accessor->get($key);
        $data_04 = array_shift($actual_key);

        // -----------------------------
        $this->assertTrue($data_01);
        $this->assertEquals($value_01, $data_02);
        $this->assertFalse($data_03);
        $this->assertFalse($data_04);
    }
    // }}}
    // {{{ test_inc_01
    public function test_inc_01()
    {
        $key     = 564;
        $offset  = 1;
        $accessor = Cascade::getAccessor(self::SCHEMA_NAME);
        $accessor->delete($key);

        // -----------------------------
        $accessor->add($key, 0);
        for ($i = 1; $i <= 100; $i++) {
            $data_01 = $accessor->increment($key, $offset);
            $this->assertEquals($data_01, $i * $offset);
        }
        // -----------------------------
        for ($i = 1; $i < 100; $i++) {
            $data_01 = $accessor->decrement($key, $offset);
            $this->assertEquals($data_01, 100 - $i * $offset);
        }
    }
    // }}}
};

/**
 *  データーフォーマット
 */
final class Cascade_Accessor_KVS_TestMemcached01_DataFormat
    extends Cascade_DB_KVS_DataFormat
{
    // ----[ Properties ]---------------------------------------------
    // @var string DSN
    protected $dsn          = 'gree(memcache)://node/test';
    // @var int    DB接続ドライバー種別
    protected $driver_type  = self::DRIVER_MEMCACHED;
    // @var string  namespace
    protected $namespace    = 'cascade#test';
    // @var boolean The data compressed flag
    protected $compressed   = TRUE;
};
