<?php
/**
 *  TestMySQLi22.php5
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade
 *  @version  $Id:$
 */

/**
 *  Cascade_Accessor_SQL_TestMySQLi21
 */
final class Cascade_Accessor_SQL_TestMySQLi22
    extends PHPUnit_Framework_TestCase
{
    // ----[ Class Constants ]----------------------------------------
    const SCHEMA_NAME = 'test#Accessor_SQL_TestMySQLi22';

    // ----[ Methods ]------------------------------------------------
    // {{{ setUp
    /**
     *  初期化処理
     */
    public /* void */
        function setUp(/* void */)
    {}
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ test_create_db
    public function test_create_db()
    {
        $accessor = Cascade::getAccessor(self::SCHEMA_NAME);
        $accessor->createDB();
    }
    // }}}
};

/**
 *  データーフォーマット
 */
final class Cascade_Accessor_SQL_TestMySQLi22_DataFormat
    extends Cascade_DB_SQL_DataFormat
{
    // ----[ Properties ]---------------------------------------------
    protected $master_dsn    = 'gree://master/test';
    protected $slave_dsn     = 'gree://slave/test';
    protected $database_name = 'test_new';
};
