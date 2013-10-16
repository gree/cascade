.. _sql_dataformat_abstract_class:

Abstract Class
=========================================================================================================
This class is defined configurations of the SQL Table informations

Class synopsis
---------------------------------------------------------------------------------------------------------

| abstract class Cascade_DB_SQL_DataFormat
|   extends Cascade_DB_DataFormat
| {
|   **// Class Constants**
|   const DRIVER_MYSQLI = Cascade::DRIVER_MYSQLI;
|   const FETCH_MODE_NUM = Cascade_DB_SQL_Statement::FETCH_MODE_NUM;
|   const FETCH_MODE_ASSOC = Cascade_DB_SQL_Statement::FETCH_MODE_ASSOC;
|
|   **// Properties for table**
|   protected *$primary_key* = ``NULL``;
|   protected *$auto_increment* = ``TRUE``;
|   protected *$updated_at_column* = ``NULL``;
|   protected *$created_at_column* = ``NULL``;
|   protected *$field_names* = array();
|
|   **// Properties for driver**
|   protected *$driver_type* = self::DRIVER_MYSQLI;
|   protected *$fetch_mode* = self::FETCH_MODE_ASSOC;
|
|   **// Properties for data source**
|   protected *$master_dsn* = ``NULL``;
|   protected *$slave_dsn* = ``NULL``;
|   protected *$extra_dsn* = array();
|
|   **// Properties for interceptor**
|   protected *$interceptors* = array(
|     'Cascade_AOP_SQL_StatementCacheInterceptor',
|   );
|
|   **// Properties for query**
|   protected *$queries* = ``NULL``;
|   protected static *$hdl_get_dynamic_query* = ``NULL``;
|
|   **// Methods**
|   public Cascade_DB_SQL_ShardSelector `getShardSelector <./methods/getshardselector.html>`__ ( void )
|   public string `getMasterDSN <./methods/getmasterdsn.html>`__ ( Cascade_DB_Criteria *$criteria* )
|   public string `getSlaveDSN <./methods/getslavedsn.html>`__ ( Cascade_DB_Criteria *$criteria* )
|   public string `getExtraDSN <./methods/getextradsn.html>`__ ( Cascade_DB_Criteria *$criteria* )
|   public string `getTableName <./methods/gettablename.html>`__ ( Cascade_DB_Criteria *$criteria* )
|   final public int `getDriverType <./methods/getdrivertype.html>`__ ( void )
|   final public int `getFetchMode <./methods/getfetchmode.html>`__ ( void )
|   final public mixed `getPrimaryKey <./methods/getprimarykey.html>`__ ( void )
|   final public mixed `getFetchKey <./methods/getfetchkey.html>`__ ( void )
|   final public mixed `getCardinalKey <./methods/getcardinalkey.html>`__ ( void )
|   final public boolean `isUseFetchKey <./methods/isusefetchkey.html>`__ ( void )
|   final public string `getActiveDSN <./methods/getactivedsn.html>`__ ( Cascade_DB_Criteria *$criteria* )
|   final public string `getDynamicQuery <./methods/getdynamicquery.html>`__ ( Cascade_DB_Criteria *$criteria* )
|   static public void `registerDynamicQueryHandler <./methods/registerdynamicqueryhandler.html>`__ ( callback *$name* )
| }

Class variables
---------------------------------------------------------------------------------------------------------
$table_name
 type:
  string
 description:
  Table name
 detail:
  Specify table name to access
 examples:
  | $table_name = 'user_data'

---------------------------------------------------------------------------------------------------------

$primary_key
 type:
  string
 description:
  PRIMARY-KEY. (if you want to define multi-column-index, set array() to this variable
 detail:
  If table-define has PRIMARY KEY, set this variable
 example:
  | $primary_key = 'id'
  | $primary_key = array('user_id', 'item_id)

---------------------------------------------------------------------------------------------------------

$auto_increment
 type:
  int
 description:
  Flag of AUTO_INCREMENT
 detail:
  If PRIMARY-KEY is AUTO_INCREMENT, set ``true``
 example:
  | $auto_increment = true

---------------------------------------------------------------------------------------------------------

$updated_at_column
 type:
  string
 description:
  Field name of updated column
 detail:
  Specify the column name to store date information record is updated. If not, specify ``NULL``
 example:
  | $updated_at_column = 'mtime'

---------------------------------------------------------------------------------------------------------

$created_at_column
 type:
  string
 description:
  Field name of created column
 detail:
  Specify the column name to store date information record is created. If not, specify ``NULL``
 example:
  | $created_at_column = 'ctime'

---------------------------------------------------------------------------------------------------------

$field_names
 type:
  array
 description:
  Column names a list of the table
 detail:
  Define a list of the SQL table column names
 example:
  | $field_names = array('id', 'user_id', 'item_id')

---------------------------------------------------------------------------------------------------------

$driver_type
 type:
  int
 description:
  Type of database connection driver
 detail:
  Currently supports DRIVER_MYSQLI only
 example:
  | $driver_type = self::DRIVER_MYSQLI

---------------------------------------------------------------------------------------------------------

$fetch_mode
 type:
  int
 description:
  fetch mode of results
 detail:
  Specifies the format of the data acquisiton results. Possible values, see below for class constants
 example:
  | $fetch_mode = self::FETCH_MODE_ASSOC

---------------------------------------------------------------------------------------------------------

$master_dsn
 type:
  string
 description:
  Master DSN
 detail:
  Specify the DSN to be selected by default master
 example:
  | $master_dsn = 'gree://master/user'

---------------------------------------------------------------------------------------------------------

$slave_dsn
 type:
  string
 description:
  Slave DSN
 detail:
  Specify the DSN to be selected by default slave
 example:
  |  $slave_dsn = 'gree://slave/user'

---------------------------------------------------------------------------------------------------------

$extra_dsn
 type:
  string
 description:
  Extended DSN
 detail:
  Specify the DSN to be selected by deafult extended. You shoud define a set of identifiers and DSN in the array
 example:
  |  $extra_dsn = array('batch' => 'gree://batch/user')
  |  $extra_dsn = array()

---------------------------------------------------------------------------------------------------------

$queries
 type:
  array
 description:
  Define queries
 detail:
  Define a dynamic query. Table names are used for the MAGIC CONTENT WARD. You can define variable by writing to BIND, [:variable name]. If you want to escape, use [\\:]. You didn't write OFFSET and LIMIT tokens.
 example:
  | $querys = array(
  |     'find_by_user' => array(
  |         'sql'=>'SELECT * FROM __TABLE_NAME__  WHERE user_id = :user_id',
  |     ),
  |     'init_uvar' => array(
  |         'sql' => 'SET @FOO \\:=NULL',
  |     ),
  | );
