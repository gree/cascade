<?php
/**
 *  TestMySQLi21.php5
 *
 *  << DataFormatの条件 >>
 *    trable_name : test_cascade
 *    primary_key : user_id, item_id
 *    fetch_key   : user_id
 *    fetch_mode  : ASSOC
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade
 *  @version  $Id:$
 */

/**
 *  Cascade_Accessor_SQL_TestMySQLi21
 */
final class Cascade_Accessor_SQL_TestMySQLi21
    extends PHPUnit_Framework_TestCase
{
    // ----[ Class Constants ]----------------------------------------
    const SCHEMA_NAME        = 'test#Accessor_SQL_TestMySQLi21';

    // ----[ Class Constants ]----------------------------------------
    const FIELD_NAME_USER_ID = 'user_id';
    const FIELD_NAME_ITEM_ID = 'item_id';
    const FIELD_NAME_NUM     = 'num';

    // ----[ Methods ]------------------------------------------------
    // {{{ setUp
    /**
     *  初期化処理
     */
    public /* void */
        function setUp(/* void */)
    {
        $this->provider();
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ test_insert_01
    public function test_insert_01()
    {
        $accessor   = Cascade::getAccessor(self::SCHEMA_NAME);
        $stmt_name = 'insert';
        $params    = array('user_id' => 200, 'item_id' => 1, 'num' => 1);
        $data_01   = $accessor->execute($stmt_name, $params, $hint = NULL);
        $params    = array('user_id' => 200, 'item_id' => null, 'num' => 1);
        $data_02   = $accessor->execute($stmt_name, $params, $hint = NULL);

        // -----------------------------
        $this->assertEquals(1, $data_01);
        $this->assertEquals(1, $data_02);
    }
    // }}}
    // {{{ test_insert_failed_1
	/**
	 * @expectedException Cascade_Exception_DriverException
	 */
    public function test_insert_failed_1()
    {
        $accessor   = Cascade::getAccessor(self::SCHEMA_NAME);
        $stmt_name = 'insert';

        // これでもできるらしい
        //$this->setExpectedException('Cascade_Exception_DriverException');

        $params    = array('user_id' => 200, 'item_id' => 2, 'num' => null);
        $data_03   = $accessor->execute($stmt_name, $params, $hint = NULL); // exceition
    }
    // }}}
    // {{{ test_insert_failed_2
    public function test_insert_failed_2()
    {
        $accessor   = Cascade::getAccessor(self::SCHEMA_NAME);
        $stmt_name = 'insert';

        $object = new stdClass();
        $object->foo = 'bar';
        $object->hoge = 'fuga';
        $params    = array('user_id' => $object, 'item_id' => 2, 'num' => null);
        try {
            $data_04   = $accessor->execute($stmt_name, $params, $hint = NULL); // exceition
        } catch (Cascade_Exception_DBException $exception) {
            $expected = "Invalid Z_TYPE of bind-value {z_type, value} object stdClass(
    foo: bar,
    hoge: fuga
)";
            $this->assertSame($expected, $exception->getMessage());
            return;
        }
        $this->fail();
    }
    // }}}
    // ----[ Methods ]------------------------------------------------
    // {{{ provider
    /**
     *  テストデータを作る
     */
    protected /* void */
        function provider(/* void */)
    {
        // テストデータを作る
        $mysqli = new Cascade_Driver_SQL_MySQLi('gree(mysql)://master/test');
        // MULTI-COLUMN-INDEX
        $mysqli->query('DROP TABLE IF EXISTS test_cascade');
        $mysqli->query($this->get_table_schema());
        $mysqli->query('INSERT INTO test_cascade VALUE(100, 1, 1, now(), now())');
        $mysqli->query('INSERT INTO test_cascade VALUE(100, 2, 2, now(), now())');
        $mysqli->query('INSERT INTO test_cascade VALUE(100, 3, 3, now(), now())');
        $mysqli->query('INSERT INTO test_cascade VALUE(100, 4, 5, now(), now())');
        $mysqli->query('INSERT INTO test_cascade VALUE(101, 1, 3, now(), now())');
        $mysqli->query('INSERT INTO test_cascade VALUE(102, 2, 1, now(), now())');
        $mysqli->query('INSERT INTO test_cascade VALUE(103, 3, 1, now(), now())');
        $mysqli->query('INSERT INTO test_cascade VALUE(104, 4, 1, now(), now())');
        $mysqli->query('INSERT INTO test_cascade VALUE(105, 1, 3, now(), now())');
        $mysqli->query('INSERT INTO test_cascade VALUE(105, 5, 4, now(), now())');
    }
    // }}}
    // {{{ get_table_schema
    /**
     *  サンプルデータのテーブルスキーマを取得する
     *
     *  @return  string  テーブル作成クエリ
     */
    protected /* string */
        function get_table_schema(/* void */)
    {
        return <<< EOD
CREATE TABLE IF NOT EXISTS test_cascade(
   user_id    INT      UNSIGNED  NOT NULL,
   item_id    SMALLINT UNSIGNED,
   num        INT                NOT NULL DEFAULT 0,
   mtime      TIMESTAMP          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
   ctime      DATETIME           NOT NULL DEFAULT '0000-00-00 00:00:00',
   UNIQUE (user_id, item_id),
   KEY   key_01(user_id, num)
) ENGINE=InnoDB
EOD;
    }
    // }}}
};

/**
 *  データーフォーマット
 */
final class Cascade_Accessor_SQL_TestMySQLi21_DataFormat
    extends Cascade_DB_SQL_DataFormat
{
    // ----[ Properties ]---------------------------------------------
    protected $table_name        = 'test_cascade';
    // @var mixed   PRIMARY-KEY   (multi-column-indexは配列で定義)
    protected $primary_key       = array('user_id', 'item_id');
    // @var mixed   データ取得KEY (NULLの場合:primary_keyを採用)
    protected $fetch_key         = 'user_id';
    // @var boolean AUTO_INCREMENTフラグ
    protected $auto_increment    = FALSE;
    // @var string  フィールド名 (更新日)
    protected $updated_at_column = 'mtime';
    // @var string  フィールド名 (作成日)
    protected $created_at_column = 'ctime';
    // @var int    DB接続ドライバー種別
    protected $driver_type       = self::DRIVER_MYSQLI;
    // @var int     結果のフェッチモード
    protected $fetch_mode        = self::FETCH_MODE_ASSOC;
    // @var string  マスターDSN
    protected $master_dsn        = 'gree://master/test';
    // @var string  スレーブDSN
    protected $slave_dsn         = 'gree://slave/test';
    // @var array   拡張DSNリスト
    protected $extra_dsn         = array(
        'standby' => 'gree://standby/test',
    );
    // @var array   フィールド名リスト
    protected $field_names       = array(
        'user_id',
        'item_id',
        'num',
        'mtime',
        'ctime',
    );
    // @var array   クエリ定義
    protected $queries = array(
        'find_by_gt_num' => array(
            'sql' => 'SELECT * FROM __TABLE_NAME__ WHERE :num <= num AND user_id != :except_user_id ORDER BY user_id, item_id',
        ),
        'find_user_by_gt_num' => array(
            'sql' => 'SELECT user_id as user, item_id as item FROM __TABLE_NAME__ WHERE :num <= num AND user_id != :except_user_id ORDER BY user_id, item_id',
        ),
        'sum_num' => array(
            'sql' => 'SELECT SUM(num) FROM __TABLE_NAME__ WHERE user_id = :user_id',
        ),
        'inc_num' => array(
            'sql' => 'INSERT INTO __TABLE_NAME__(user_id, item_id, num, ctime)
                        VALUES(:user_id, :item_id, :diff_num, NOW()) ON DUPLICATE KEY UPDATE
                        num = @after_num := ((@before_num := num) + :diff_num)',
         ),
        '__inc_num__' => array(
            'sql' => 'SELECT @before_num, @after_num',
        ),
        'insert' => array(
            'sql' => 'INSERT INTO __TABLE_NAME__(user_id, item_id, num, ctime) VALUES(:user_id, :item_id, :num, NOW())',
         ),
    );
};
