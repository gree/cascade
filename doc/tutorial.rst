.. highlight:: php-inline
   :linenothreshold: 5

Tutorial
###############################

First step of Cascade
**********************************************

Preparation
=====================================================
The preparation to use the library is very simple. ::

  <?php
    require_once 'Cascade.php';

(*) You should investigate include_path if you have problems such as no existence of necessary files.

.. code-block:: text

   sp1rytus@cadam-01:~/cascade/manual$ php -i | grep include_path
   include_path => .:/home/gree-common/src:/usr/share/php:.....

You should ensure that Cascade Library is located in the PATH that can be dynamically resolved.

**Required definitions for access to the data**

* Definition of DSN (etc/mysql.ini.php)
* Create database
* Definition of schema (etc/cascade.ini.php)
* Definition of data structure class (DataFormat)

Definition of DSN
=====================================================
  +---------------+---------------+
  | Environment   |  MySQL        |
  +---------------+---------------+
  | Database name | cascade_test  |
  +---------------+---------------+
  | Table name    | item          |
  +---------------+---------------+

Add to /home/gree/etc/mysql.ini.php  ::

  return $db_config_list = array(
    ...
    'cascade_test' => array(
        'master'  => '192.168.1.10',
        'slave'   => '192.168.1.11',
        'standby' => '192.168.1.11',
        'db'      => 'cascade_test',
    ),
    ...
  );


Create database
=====================================================
Procedure of sample data creation

.. code-block:: text

  $ mysql -uroot -p -h192.168.1.10

  mysql> CREATE DATABSE cascade_test;
  mysql> USE cascade_test;

  mysql> CREATE TABLE IF NOT EXISTS item (
    id         INT      UNSIGNED  NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id    INT      UNSIGNED  NOT NULL,
    item_id    SMALLINT UNSIGNED  NOT NULL,
    num        INT                NOT NULL DEFAULT 0,
    mtime      TIMESTAMP          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ctime      DATETIME           NOT NULL DEFAULT '0000-00-00 00:00:00',
    UNIQUE KEY ukey_01(user_id, item_id),
    KEY         key_01(user_id, num)
  ) ENGINE=InnoDB;

  mysql> TRUNCATE TABLE item;
  mysql> INSERT INTO item VALUE(default, 100, 1, 1, now(), now());
  mysql> INSERT INTO item VALUE(default, 100, 2, 2, now(), now());
  mysql> INSERT INTO item VALUE(default, 100, 3, 3, now(), now());
  mysql> INSERT INTO item VALUE(default, 100, 4, 5, now(), now());
  mysql> INSERT INTO item VALUE(default, 101, 1, 3, now(), now());
  mysql> INSERT INTO item VALUE(default, 102, 2, 1, now(), now());
  mysql> INSERT INTO item VALUE(default, 103, 3, 1, now(), now());
  mysql> INSERT INTO item VALUE(default, 104, 4, 1, now(), now());
  mysql> INSERT INTO item VALUE(default, 105, 1, 3, now(), now());
  mysql> INSERT INTO item VALUE(default, 105, 5, 4, now(), now());

Sample data

  +----+---------+---------+-----+---------------------+---------------------+
  | id | user_id | item_id | num | mtime               | ctime               |
  +====+=========+=========+=====+=====================+=====================+
  |  1 |     100 |       1 |   1 | 2011-02-15 11:27:30 | 2011-02-15 11:27:30 |
  +----+---------+---------+-----+---------------------+---------------------+
  |  2 |     100 |       2 |   2 | 2011-02-15 11:27:30 | 2011-02-15 11:27:30 |
  +----+---------+---------+-----+---------------------+---------------------+
  |  3 |     100 |       3 |   4 | 2011-02-15 11:27:31 | 2011-02-15 11:27:30 |
  +----+---------+---------+-----+---------------------+---------------------+
  |  4 |     100 |       4 |   5 | 2011-02-15 11:27:30 | 2011-02-15 11:27:30 |
  +----+---------+---------+-----+---------------------+---------------------+
  |  5 |     101 |       1 |   3 | 2011-02-15 11:27:30 | 2011-02-15 11:27:30 |
  +----+---------+---------+-----+---------------------+---------------------+
  |  6 |     102 |       2 |   1 | 2011-02-15 11:27:30 | 2011-02-15 11:27:30 |
  +----+---------+---------+-----+---------------------+---------------------+
  |  7 |     103 |       3 |   1 | 2011-02-15 11:27:30 | 2011-02-15 11:27:30 |
  +----+---------+---------+-----+---------------------+---------------------+
  |  8 |     104 |       4 |   1 | 2011-02-15 11:27:30 | 2011-02-15 11:27:30 |
  +----+---------+---------+-----+---------------------+---------------------+
  |  9 |     105 |       1 |   3 | 2011-02-15 11:27:30 | 2011-02-15 11:27:30 |
  +----+---------+---------+-----+---------------------+---------------------+
  | 10 |     105 |       5 |   4 | 2011-02-15 11:27:30 | 2011-02-15 11:27:30 |
  +----+---------+---------+-----+---------------------+---------------------+
  | 12 |     200 |       5 |   3 | 2011-02-15 11:27:31 | 2011-02-15 11:27:31 |
  +----+---------+---------+-----+---------------------+---------------------+


Definition of schema
=====================================================

Relation of schema and accessor
--------------------------------------------------------------
| Cascade schema name is required for acquiring the accessor for data access.
| The accessor has been designed for acquiring as follows.

Example of acquiring accessor ::

  $ac = Cascade::getAccessor(${schema name});

+----------------+------------------------------------------------+
| ${schema name} | ${namespace}${separator}${identifier}          |
+----------------+------------------------------------------------+
| ${separator}   | default separator is '#'                       |
+----------------+------------------------------------------------+
| ${namespace}   | Value in specifies service (ex. sample)        |
+----------------+------------------------------------------------+
| ${identifier}  | Value in data structure class name (ex. item)  |
+----------------+------------------------------------------------+

Example of the schema definition ::

  'xxx : default' => array(
    'dataformat.prefix'  => 'Service_XXX_Cascade_DataFormat',
    'gateway.prefix'     => 'Service_XXX_Cascade_Gateway',
    ),

  At this setting

    Cascade::getAccessor('xxx#user_item')
    => Service_XXX_Cascade_DataFormat_User_Item
    => Service_XXX_Cascade_Gateway_User_Item

  Class name is resolved like this.

  ('_' is recognized as a directory separator. )

Definition of schema
--------------------------------------------------------------
| The class definition for PHP is arranged in */home/gree/service/sample/class*.
| The relation between the class name and the file PATH is defined as
|   *class Gree_Service_Sample_XXX_XXX => /home/gree/service/sample/class/XXX/XXX.php*.
| The schema is setting as follows.

/home/gree/etc/cascade.ini.php ::

  return $cascade_config = array(
    CASCADE_CONFIG_INDEX_SCHEMA => array(
       ...
       'sample : default' => array(
           'dataformat.prefix'  => 'Gree_Service_Sample_Cascade_DataFormat',
           'dataformat.suffix'  =>  null,
           'gateway.prefix'     => 'Gree_Service_Sample_Cascade_Gateway',
           'gateway.suffix'     =>  null
           'load.path'          => '/home/gree/service/sample',
           'load.ignore_prefix' => 'Gree_Service_Sample',
           'load.file_ext'      => '.php',
       ),
       ...
    ),
  );

 The meaning that 'sample : default' is defined as sample that inherited from the defined value of the default schema.


Definition of DataFormat
=====================================================
| DataFormat class definition is a table information of access target.
| It is required to define for each table. (Except divided tables)
|
| Example of DataFormat (item table)

/home/gree/service/sample/class/Cascade/DataFormat/Item.php ::

  class Gree_Service_Sample_Cascade_DataFormat_Item extends Cascade_DB_SQL_DataFormat
  {
      // Table name
      protected $table_name        = 'item';
      // PRIMARY-KEY   (multi-column-index are defined by array)
      protected $primary_key       = 'id';
      // Data fetch KEY (primary_key is used at NULL)
      protected $fetch_key         = NULL;
      // AUTO_INCREMENT flag
      protected $auto_increment    = TRUE;
      // Field name (modified time)
      protected $updated_at_column = 'mtime';
      // Field name (create time)
      protected $created_at_column = 'ctime';
      // Master DSN
      protected $master_dsn        = 'gree://master/cascade_test';
      // Slave DSN
      protected $slave_dsn         = 'gree://slave/cascade_test';
      // Field name list
      protected $field_names       = array(
          'id',        // ID
          'user_id',   // User ID
          'item_id',   // Item ID
          'num',       // having #
          'mtime',     // modified time
          'ctime',     // create time
      );
      // Definition of query
      protected $queries = array(
          'find_by_user' => array(
              'sql' => 'SELECT * FROM __TABLE_NAME__ WHERE user_id = :user_id',
          ),
      );
  };

Data access
=====================================================
Data access is executed as follows

Example of access ::

  $ac = Cascade::getAccessor('sample#item')

  // Get data by PRIMARY-ID
  $item = $ac->get($id = 1);

  // Multi get by PRIMARY-ID list
  $item_hash = $ac->mget($idl = array(1, 2, 3));

  // Execute query
  $item_hash = $ac->find('find_by_user', $param = array('user_id' => 100));



Basic usage
**********************************************

MySQL
=====================================================

Usage
-----------------------------------------

**example)**

Definition of MySQL table

.. code-block:: text

    CREATE TABLE `item` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `user_id` int(10) unsigned NOT NULL,
        `item_id` int(10) unsigned NOT NULL,
        `num` int(10) unsigned NOT NULL DEFAULT '0',
        `mtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `ctime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB

Definition of DataFormat ::

 class Service_Cascade_DataFormat_Item extends Cascade_DB_SQL_DataFormat
 {
     // Table name
     protected $table_name        = 'item';
     // PRIMARY-KEY   (multi-column-index are defined by array)
     protected $primary_key       = 'id';
     // Data fetch KEY (primary_key is used at NULL)
     protected $fetch_key         = NULL;
     // AUTO_INCREMENT flag
     protected $auto_increment    = true;
     // Field name (modified time)
     protected $updated_at_column = 'mtime';
     // Field name (create time)
     protected $created_at_column = 'ctime';
     // Master DSN
     protected $master_dsn        = 'gree://master/item';
     // Slave DSN
     protected $slave_dsn         = 'gree://slave/item';
     // Field name list
     protected $field_names       = array(
         'id',        // ID
         'user_id',   // User ID
         'item_id',   // Item ID
         'num',       // Having number
         'mtime',     // modified time
         'ctime',     // create time
     );
     // Query definition
     protected $queries = array(
         'find_by_user' => array(
             'sql' => 'SELECT * FROM __TABLE_NAME__ WHERE user_id = :user_id',
         ),
         'update_num' => array(
             'sql' => 'UPDATE __TABLE_NAME__ SET num=:num WHERE user_id = :user_id',
         ),
     );
 };

Access example ::

	$cascade = Cascade::getAccessor('service#item');

	/***** -- get() : select data by primary_key (used fetch_key if defined) *****/

	// Get data from defined table (id='1')
	$result = $cascade->get('1');

	/***** -- find() : Defined SQL is called. uses for SELECT.  *****/

	// Find data by SQL defined 'find_by_user', condition is user_id=100
	$result = $casacde->find('find_by_user', array('user_id'=>100));

	// Find data by SQL defined 'find_by_user', condition is user_id=100, offset is 5, get number is 10
	$result = $casacde->find('find_by_user', array('user_id'=>100), 5, 10);

	/***** -- execute() : Defined SQL is called. uses for INSERT/UPDATE/DELETE. *****/

	// Update date by SQL defined 'update_num', user_id is 100, num is 10
	// execute return  accected rows.
	$result = $casacde->execute('update_num', array('user_id'=>100, 'num' => 10));



Sharding
-----------------------------------------
Sharding is used for database division.
Explain the sharding function.

ShardSelector
-----------------------------------------

Cascade_DB_SQL_ShardSelector 
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

* The shard selector is dividing data by id hash.
* It is possible to divide by an arbitrary key, arbitrary number of tables and arbitrary number of shards by using this class.

**example)**

character_id is the key of dividing tables. In the following example, the table is divided into 128 parts and dsn is divided into 8 parts. ::

	class Application_Cascade_ShardSelector extends Cascade_DB_SQL_ShardSelector
	{
	    /**
	     *  devide key
	     *  @var  string
	     */
	    protected $index_criteria_params = 'character_id';

	    /**
	     *  number of dsn division
	     *  @var  int
	     */
	    protected $division_count_dsn    = 8;

	    /**
	     *  number of table division
	     *  @var  int
	     */
	    protected $division_count_table  = 128;

	    /**
	     *  format of dsn suffix
	     *  @var  string
	     */
	    protected $format_suffix_dsn     = '_%d';

	    /**
	     *  format of table suffix
	     *  @var  string
	     */
	    protected $format_suffix_table   = '_%d';
	};

DataFormat is overriding getShardSelector() and returning the object of ShardSelector instance.

	class Application_Cascade_DataFormat_Character extends Cascade_DB_SQL_DataFormat
	{
	     protected $table_name        = 'character';
	     protected $primary_key       = 'character_id';
	     protected $fetch_key         = null;
	     protected $auto_increment    = true;
	     protected $updated_at_column = 'mtime';
	     protected $created_at_column = 'ctime';
	     protected $master_dsn        = 'gree://master/cascade_test';
	     protected $slave_dsn         = 'gree://slave/cascade_test';
	     protected $field_names       = array(
	         'character_id',	// Character ID
	         'param',   		// Paramators
	         'item_id',   		// Item ID
	         'num',       		// having #
	         'mtime',     		// modified time
	         'ctime',     		// create time
	     );

	    public /* string */
	        function getShardSelector(/* void */)
	    {
	        return new Application_Cascade_ShardSelector;
	    }
	};

results:

* table_id : character_id % 128
* dsn_id   : (table_id % 8) + 1

* dsn   : gree://(master|slave)/cascade_test_{dsn_id}
* table : character_{table_id}

Custom ShardSelector 
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
An original implementation is using Custom ShardSelector except hash division.

**example)**

Date (ex YYYYMMDD) is used in suffix for the table and dsn is selected in 1-4 at random.  ::

	class Application_Cascade_Custom_ShardSelector extends Cascade_DB_SQL_ShardSelector
	{
	    // {{{ getDSNSuffix
	    /**
	     *  get DSN suffix string
	     *
	     *  @param   Cascade_DB_Criteria  criteria
	     *  @return  string               dsn suffix
	     */
	    public /* string */
	        function getDSNSuffix(Cascade_DB_Criteria $criteria)
	    {
	        $suffix = '_' . mt_rand(1, 4);
	        return $suffix;
	    }
	    // }}}
	    // {{{ getTableNameSuffix
	    /**
	     *  get table name suffix string
	     *
	     *  @param   Cascade_DB_Criteria  criteria
	     *  @return  string               table suffix
	     */
	    public /* string */
	        function getTableNameSuffix(Cascade_DB_Criteria $criteria)
	    {
	        $suffix = '_' . date('Ymd');
	        return $suffix;
	    }
	    // }}}
	};

DataFormat is overriding getShardSelector() and returning the object of ShardSelector instance. ::

	class Application_Cascade_DataFormat_Character extends Cascade_DB_SQL_DataFormat
	{
	     protected $table_name        = 'character';
	     protected $primary_key       = 'character_id';
	     protected $fetch_key         = null;
	     protected $auto_increment    = true;
	     protected $updated_at_column = 'mtime';
	     protected $created_at_column = 'ctime';
	     protected $master_dsn        = 'gree://master/cascade_test';
	     protected $slave_dsn         = 'gree://slave/cascade_test';
	     protected $field_names       = array(
	         'character_id',	// Character ID
	         'param',   		// Paramators
	         'item_id',   		// Item ID
	         'num',       		// having #
	         'mtime',     		// modified time
	         'ctime',     		// create time
	     );

	    public /* string */
	        function getShardSelector(/* void */)
	    {
	        return new Application_Cascade_ShardSelector;
	    }
	};

results:

* dsn_id   : 1-4 (random)

* dsn   : gree://(master|slave)/cascade_test_{dsn_id}
* table : character_{YYMMDD}

Case of defining method in DataFormat
-----------------------------------------

* Settings of high flexibility become by overriding the method of getting DSN name for DataFormat and table name.
* It is used when it cannot use with ShardSelector.

Divide DSN
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. code-block:: text

	Cascade_DB_SQL_DataFormat::getMasterDSN
	Cascade_DB_SQL_DataFormat::getSlaveDSN

* Override these methods and DSN used is specified.
* The item of the configuration file is read by using returned DSN, and Cascade is accessed to mysqld. 

**example)**

* This example is getMasterDSN, same as getSlaveDSN.

 :: 

   public function getMasterDSN(Cascade_DB_SQL_Criteria $criteria)
   {
       $params = $criteria->params;
       $hint   = $criteria->hint;
       $id = 0;
       if (is_array($params) && (array_key_exists('id', $params))) {
           // Include 'id' on params
           $id = $criteria->params['id'];
       } else if (is_array($hint) && (array_key_exists('id', $hint))) {
           // Include 'id' on hint
           $id = $hint['id'];
       } else if (is_numeric($params)) {
           // Params is id
           $id = $params;
       } else {
           // Id doesn't exist
           throw new Service_Exception(__METHOD__." invalid criteria");
       }

       $farm = $id % seld::DSN_DIVIDE_NUM;	// DSN_DIVIDE_NUM = 4

       return ($this->master_dsn . '_' . $farm);
   }

notice
* 'id' is must included in params or hint.
* Division on various conditions is possible by the change of the farm_id calculation method.

Divide table
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Method of specifying the table to access.

.. code-block:: text

	Cascade_DB_SQL_DataFormat::getTableName

* Specify the table name which override above method. 
* Replace placeholder __TABLE_NAME__, returned table name. 

**example) How to divide into 128 using the value of 'id' specified as $params or $hint**

* Division of table is attained by the same method as farm division. 
* $table_name_[0-127] returns. 

 :: 

   public function getTableName(Cascade_DB_SQL_Criteria $criteria)
   {
       $params = $criteria->params;
       $hint   = $criteria->hint;
       $id = 0;
       if (is_array($params) && (array_key_exists('id', $params))) {
           // 'id' included $params
           $id = $criteria->params['id'];
       } else if (is_array($hint) && (array_key_exists('id', $hint))) {
           // 'id' included $hint
           $id = $hint['id'];
       } else if (is_numeric($params)) {
           // $params is used as 'id'
           $id = $params;
       } else {
           // 'id' not found : Updating is impossible. 
           throw new Service_Exception(__METHOD__." invalid criteria");
       }
       
       $farm = $id % seld::TABLE_DIVIDE_NUM;	// TABLE_DIVIDE_NUM = 128
       
       return ($this->table_name . '_' . $farm);
   }



Divide table by date
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
method of changing the table to access by a day.

.. code-block:: text

	Cascade_DB_SQL_DataFormat::getTableName

* Specify the table name which override above method. 

**example) table is divided by each date**

* stores in table of the appointed day by requesting by putting the date into $hint. 

 ::

   public function getTableName(Cascade_DB_SQL_Criteria $criteria)
   {
       $params = $criteria->params;
       $hint   = $criteria->hint;
       
       $date = 0;
       if (is_array($params) && (array_key_exists('date', $params))) {
           // 'date' included $params
           $date = $params['date'];
       } else if (is_array($hint) && (array_key_exists('date', $hint))) {
           // 'date' included $hint
           $date = $hint['date'];
       } else {
           // 'date' not found : use default date
           $date = '20110101';
       }
       
       $tablename = $this->table_name . '_' . $date;
       return $tablename;
   }

Present date can also be used when date does not exist in params and hint. 

 ::

    } else {
        $date = date('Ymd'); 
    }

Expanded DSN
-----------------------------------------
* Cascade supports access function of "Expanded DSN" by assign role to each slaves.
* By using this function, it becomes possible to create slaves only for batch/support, and it also becomes possible to switch slaves of each different indexes depends on the SQL query.

**example) setting slave server for batch program**

.. code-block:: text

	db-master	192.168.1.100
	db-slave1	192.168.1.101
	db-slave2	192.168.1.102
	db-slave3	192.168.1.103
	db-slave4	192.168.1.104

In these composition, db-slave4 is set up as only for batch access.

config :: 

 'test' => array(
     'master'	=> '192.168.1.100',
     'slave'   	=> array(
         '192.168.1.101',
         '192.168.1.102',
         '192.168.1.103',
     ),
     'batch'     => array(
         '192.168.1.104',
     ),
     'db'      => 'test',
 ),

Ex method is used at the time of access. :: 

 $cascade->find('find_data', $params, $offset, $limit, $hint);
   ::::
 $cascade->findEx('batch', 'find_data', $params, $offset, $limit, $hint);

* executeEx does not exist because the Expanded DSN is reference only.

KVS
=====================================================

Basic usage
-----------------------------------------

In the example of memcached access explains how to access to the KVS.

**example) A setup of memcached for caching the data of "character"**

Definition of DataFormat ::

    class Service_Cascade_DataFormat_Character extends Cascade_DB_KVS_DataFormat
    {
        // ----[ Properties ]---------------------------------------------
        // @var string DSN
        protected $dsn          = 'gree(memcache)://node/character';
        // @var int    connection driver
        protected $driver_type  = self::DRIVER_LIBMEMCACHED;
        // @var string  namespace
        protected $namespace    = 'service#character';
        // @var boolean The data compressed flag
        protected $compressed   = true;
    };


Access example ::

    $cascade = Cascade::getAccessor('service#character');
    
    /***** --- data get by get() *****/
    
    // get data : key = 1 
    list($data, $token) = $cascade->get('1');
    
    // get data : key = 'test1'
    list($data, $token) = $cascade->get('test1');
    
    /***** --- store data by set() *****/
    
    // set value 'character1' for key = 1
    $cascade->set('1', 'character1');


cas
-----------------------------------------
* CAS (Compare-and-Swap) tries atomic operation to data.
* By using token acquired at the time to get, cas checks the update of by others until the data will updated. The update fails if the data is already updated by others.

**example) The value of the item 'data' stored in KVS is modified and saved**

 :: 

    public function compareAndSwap($key, $data)
    {
        $cascade = Cascade::getAccessor('service#data');
        
        $result = false;
        // The number of CAS_RETRY times repeats. 
        for ($i = 0 ; $i < self::CAS_RETRY ; $i++) {
            list($value, $token) = $cascade->get($key);
            $value['data'] = $data;
            
            $ret = $cascade->cas($token, $key, $value);
            if ($ret !== false) {
                // success
                $result = true;
                break;
            }
            // If it fails, it will redo from get (new token is acquired).   
        }
        
        return $result;
    }


increment / decrement
-----------------------------------------

* increment/decrement treats data as a numerical value and performs addition and subtraction.

**example) The number of times of login is saved in KVS**

Definition of DataFormat ::

    class Service_Cascade_DataFormat_Logincount extends Cascade_DB_KVS_DataFormat
    {
        // ----[ Properties ]---------------------------------------------
        // @var string DSN
        protected $dsn          = 'gree(memcache)://node/logincount';
        // @var int    connection driver
        protected $driver_type  = self::DRIVER_LIBMEMCACHED;
        // @var string  namespace
        protected $namespace    = 'service#logincount';
        // @var boolean The data compressed flag
        protected $compressed   = false;
    };

Access example ::

    $cascade = Cascade::getAccessor('service#logincount');
    // The data of key=1 is added. The value after addition goes into $count. 
    $count = $cascade->increment('1');

Config file
=====================================================

ini
-----------------------------------------
* Procedure of the ini file in cascade. 
* Support parse_ini_file() format. 

**example)**

Definition of the config file (sample.ini)

.. code-block:: text

 [production]
 webhost                  = www.example.com
 database.adapter         = pdo_mysql
 database.params.host     = db.example.com
 database.params.username = dbuser
 database.params.password = secret
 database.params.dbname   = dbname

Definition of DataFormat ::

    class Service_Cascade_DataFormat_Sample extends Cascade_DB_Config_DataFormat
    {
        // ------------------------ Attributes ---------------------------
        // @var string  directory of config file
        protected $config_path  = /path/to/config/directory;
        // @var string  name of config file
        protected $config_file  = 'sample.ini';
        // @var int    driver
        protected $driver_type  = self::DRIVER_INIFILE;
        // @var int    fetch mode of result
        protected $fetch_mode   = self::FETCH_MODE_ASSOC;
    
        // ---------------------------------------------------------------
    }

Access example

get 'production' ::

 $cascade = Cascade::getAccessor('service#sample');
 $cascade->get('production');

result ::

 array(
     'webhost'           => 'www.example.com',
     'database' => array(
         'adapter'       => 'pdo_mysql',
         'params' => array(
             'host'      => 'db.example.com',
             'username'  => 'dbuser',
             'password'  => 'secret',
             'dbname'    => 'dbname',
         ),
     ),
 );
 
get 'database' in 'production'  :: 

 $cascade = Cascade::getAccessor('service#sample');
 $cascade->get('production', 'database');

result :: 

 array(
    'adapter'       => 'pdo_mysql',
    'params' => array(
        'host'      => 'db.example.com',
        'username'  => 'dbuser',
        'password'  => 'secret',
        'dbname'    => 'dbname',
    ),
 );

get 'database.params.host' in 'production' :: 
 
 $cascade = Cascade::getAccessor('service#sample');
 $cascade->get('production', 'database.params.host');

result :: 

 'db.example.com'

**example) Inheritance of a value**

Definition of the config file (sample.ini)

.. code-block:: text

 [production]
 webhost                  = www.example.com
 database.adapter         = pdo_mysql
 database.params.host     = db.example.com
 database.params.username = dbuser
 database.params.password = secret
 database.params.dbname   = dbname
 
 [staging : production]
 database.params.host     = dev.example.com
 database.params.username = devuser
 database.params.password = devsecret

Access example

get 'staging' ::

 $cascade = Cascade::getAccessor('service#sample');
 $cascade->get('staging');

result :: 

 array(
     'webhost'           => 'www.example.com',
     'database' => array(
         'adapter'       => 'pdo_mysql',
         'params' => array(
             'host'      => 'dev.example.com',
             'username'  => 'devuser',
             'password'  => 'devsecret',
             'dbname'    => 'dbname',
         ),
     ),
 );

get 'database.params.host' in staging ::

 $cascade = Cascade::getAccessor('service#sample');
 $cascade->get('staging', 'database.params.host');

result :: 

 'dev.example.com'

array
-----------------------------------------
* Procedure of the php array file in cascade. 

**example)**

Definition of the config file (sample.php) :: 

 return array(
     1 => array(
         'id'        => 10001,
         'value'     => 3,
         'comment'   => 'test 1',
     ),
     2 => array(
         'id'        => 10002,
         'value'     => 100,
         'comment'   => 'test 2',
     ),
     'test' => array(
         'value'     => 'test test',
     ),
 );

Definition of DataFormat ::

    class Service_Cascade_DataFormat_Sample extends Cascade_DB_Config_DataFormat
    {
        // ------------------------ Attributes ---------------------------
        // @var string  directory of config file
        protected $config_path  = /path/to/config/directory;
        // @var string  name of config file
        protected $config_file  = 'sample.php';
        // @var int     driver = self::DRIVER_PHPARRAY
        protected $driver_type  = self::DRIVER_PHPARRAY;
        // @var int     fetch mode of result
        protected $fetch_mode   = self::FETCH_MODE_ASSOC;
    
        // ---------------------------------------------------------------
    }

Access example

get data key=1 ::

 $cascade = Cascade::getAccessor('service#sample');
 $cascade->get(1);

result ::

 array(
     'id'        => 10001,
     'value'     => 3,
     'comment'   => 'test 1',
 );

get data key='test' ::

 $cascade = Cascade::getAccessor('service#sample');
 $cascade->get('test');

result ::

 array(
     'value'     => 'test test',
 );

csv
-----------------------------------------
* Procedure of the CSV(Comma Separated Values) file in cascade. 
* The first line of csv serves as an item name. The first row is set to key. 

**example)**

Definition of the config file (sample.csv)

.. code-block:: text

 id,kind,min,max,comment
 1,1,10,100,test1
 2,1,11,200,test2
 3,2,10,200,test3
 10,3,100,200,test test

Definition of DataFormat ::

    class Service_Cascade_DataFormat_Sample extends Cascade_DB_Config_DataFormat
    {
        // ------------------------ Attributes ---------------------------
        // @var string  directory of config file
        protected $config_path  = /path/to/config/directory;
        // @var string  name of config file
        protected $config_file  = 'sample.csv';
        // @var int     driver = self::DRIVER_CSVFILE
        protected $driver_type  = self::DRIVER_CSVFILE;
        // @var int     fetch mode of result
        protected $fetch_mode   = self::FETCH_MODE_ASSOC;
    
        // ---------------------------------------------------------------
    }

Access example

get data key=1 ::

 $cascade = Cascade::getAccessor('service#sample');
 $cascade->get(1);

result :: 

 array(
     'id'        => 1,
     'kind'      => 1,
     'min'       => 10,
     'max'       => 100,
     'comment'   => 'test1',
 );

get data key=10 ::

 $cascade = Cascade::getAccessor('service#sample');
 $cascade->get(10);

result :: 

 array(
     'id'        => 10,
     'kind'      => 3,
     'min'       => 100,
     'max'       => 200,
     'comment'   => 'test test',
 );

**example) Text key**

Definition of the config file (sample.csv)

.. code-block:: text

 id,kind,min,max,comment
 level1,1,10,100,test1
 level2,1,11,200,test2
 level3,2,10,200,test3
 level4,3,100,200,test test

Access example

get data key='level1' ::

 $cascade = Cascade::getAccessor('service#sample');
 $cascade->get('level1');

result :: 

 array(
     'id'        => 'level1',
     'kind'      => '1',
     'min'       => '10',
     'max'       => '100',
     'comment'   => 'test1',
 );


get data key='level4'

 $cascade = Cascade::getAccessor('service#sample');
 $cascade->get('level4');

result ::

 array(
     'id'        => 'level4',
     'kind'      => '3',
     'min'       => '100',
     'max'       => '200',
     'comment'   => 'test test',
 );


Data Gateway
**********************************************

* Access to the DataFormat is performed via Gateway, not directly.
* Using path through gateway when gateway is not defined.
* Use Gateway in order to encapsulate processing of a data layer. By using Gateway, the logic of data access can be separated from the logic of a game.


Path Through Gateway (default) 
=====================================================

* Gateway for calling DataFormat without doing anything.
* Using it when Gateway is not defined. 

Local Cache Gateway 
=====================================================

* Gateway for caching in APC the data acquired from MySQL. 
* For example, using it for caching master data. 

**example) The item master data of MySQL are cached in APC of a local server**

Definition of DataFormat

.. code-block:: php-inline

    class Service_Cascade_DataFormat_Master_Item extends Cascade_DB_SQL_DataFormat
    {
        // ------------------------ Attributes ---------------------------
        // @var string  master DSN
        protected $master_dsn        = 'gree://master/master';
        // @var string  slave DSN
        protected $slave_dsn         = 'gree://slave/master';
        // @var array   extra DSN
        protected $extra_dsn         = array();
        // @var mixed   Primary key
        protected $primary_key       = 'id';
        // @var mixed   Data fetch key
        protected $fetch_key         = NULL;
        // @var boolean AUTO_INCREMENT fkag
        protected $auto_increment    = false;
        // @var string  modify date 
        protected $updated_at_column = 'mtime';
        // @var string  create date
        protected $created_at_column = 'ctime';
        // @var string  table name
        protected $table_name        = 'item_master';
        // @var array   field name
        protected $field_names       = array(
            'id',           // ItemID
            'name',         // Item name
            'category',     // category
            'effect_id',    // effect ID
            'effect_value', // effect value
            'state',        // status
            'mtime',        // modified time
            'ctime',        // created time
        );
        // @var array   query
        protected $queries = array(
        );

        // ---------------------------------------------------------------
    }

Definition of Gateway

.. code-block:: php-inline

    class Service_Cascade_Gateway_Master_Item extends Cascade_Proxy_ReadLocalCacheGateway
    {
    }

Access example

.. code-block:: php-inline

    $cascade->get(1);    // If there is no data in APC, it acquires from mysql and caches in APC. 

Notice

* expire time is not set
* apache reload is required to clear data. 
* Please be careful of the capacity of APC. 
* execute is not support for safety. 

Custorm Gateway 
=====================================================

* Customizable Gateway (base class). 
* This class is inherited and created to define Gateway uniquely. 

Override method
-----------------------------------------

* The existing method can be overridden, therefore the process of the method can be modify.

example) Read through cache

Definition of DataFormat(MySQL) ::

    class Service_Cascade_DataFormat_User extends Cascade_DB_SQL_DataFormat
    {
        // ------------------------ Attributes ---------------------------
        // @var string  master DSN
        protected $master_dsn        = 'gree://master/user';
        // @var string  slave DSN
        protected $slave_dsn         = 'gree://slave/user';
        // @var array   extra DSN
        protected $extra_dsn         = array();
        // @var mixed   Primary key
        protected $primary_key       = 'user_id';
        // @var mixed   Data fetch key
        protected $fetch_key         = NULL;
        // @var boolean auto increment flag
        protected $auto_increment    = false;
        // @var string  modified time 
        protected $updated_at_column = 'mtime';
        // @var string  created time 
        protected $created_at_column = 'ctime';
        // @var string  table name
        protected $table_name        = 'user';
        // @var array   field name list
        protected $field_names       = array(
            'user_id',      // user ID
            'name',         // name
            'age',          // age
            'state',        // status
            'profile',      // profile
            'mtime',        // modified time
            'ctime',        // created time
        );
        // @var array   definition of queries
        protected $queries = array(
            'update_age' => array(
                'sql' => 'UPDATE __TABLE_NAME__ SET age=:age WHERE user_id=:user_id',
            ),
            'find_by_status' => array(
                'sql' => 'SELECT * FROM __TABLE_NAME__ WHERE state=:state',
            ),
        );
    }


Definition of DataFormat(memcached) ::

    class Service_Cascade_DataFormat_Cache_User extends Cascade_DB_KVS_DataFormat
    {
        // @var string DSN
        protected $dsn          = 'gree(memcache)://node/user';
        // @var int    driver
        protected $driver_type  = self::DRIVER_LIBMEMCACHED;
        // @var string  namespace
        protected $namespace    = 'service#user';
        // @var boolean The data compressed flag
        protected $compressed   = true;
    };

Definition of Gateway ::

    class Service_Cascade_Gateway_User extends Cascade_Proxy_CustomGateway  // Inheritance CustomGateway base class
    {
        protected $expiretime   = 1800; // memcached expire time : default: 30min
		
        /***
         * get method
         */
        public function get($key, $hint=null, $use_master=false)
        {
            // read from memcached
            $cache_session = null;
            $schema_name = $this->namespace . '#cache_' . $this->identifier; // schema_name : {namespace}#cache_user
            try {
                // access to memcached
                $cache_session = Cascade::getAccessor($schema_name); // get memcached accessor
                if ($cache_session !== null) {
                    list($value, $token) = $cache_session->get($key); // data gett
                    if ($value !== false) {
                        // hit cache
                        return $value;
                    }
                }
            } catch (Exception $e) {
                // Ignore exception when access memcached
                trigger_error(__METHOD__." : catch exception : " . $e->getMessage());
            }
			
            // Data read from MySQL
            // this->session is accessor to mysqld (becouse {namespace}#user is mysqld DataFoemat)
            $value = $this->session->get($key, $hint, $use_master); // Get data
			
            // Data store to memcached
            if ($cache_session !== null) {
                try {
                    $cache_session->set($key, $value, $this->expiretime); // Store data
                } catch  (Exception $e) {
                    // Ignore exception when access memcached
                    trigger_error(__METHOD__." : catch exception : " . $e->getMessage());
                }
            }
            return $value;
        }
		
        /***
         * get method (clear cache data)
         */
        public function execute($stmt_name, $params=null, $hint=null)
        {
            // find key
            $df = Cascade::getDataFormat($this->schema_name);   // get dataformat class
            $keyname = $df->getCardinalKey();   // get key (primary key if defined)
            $key = false;
            if (is_string($keyname)) {
                // key is enabled
                $key = $this->_searchKey($keyname, $params, $hint);
            }
			
            if ($key) {
                // clear cache data
                $cache_session = null;
                $schema_name = $this->namespace . '#cache_' . $this->identifier; // memcached schema_name : {namespace}#cache_user
                try {
                    $cache_session = Cascade::getAccessor($schema_name); // get accessor for memcached
                    if ($cache_session !== null) {
                        $cache_session->delete($key);   // delete data
                    }
                } catch (Exception $e) {
                    // ignore errorx
                    trigger_error(__METHOD__." : catch exception : " . $e->getMessage());
                }
            }
			
            // execute request to mysqld (this->session is accessor for mysqld)
            return $this->session->execute($stmt_name, $params, $hint);
        }
		
        /***
         * Find key from params or hint
         */
        protected function _searchKey($keyname, $params=null, $hint=null)
        {
            $key = false;
            // Find key from params or hint
            if (is_array($params) && (array_key_exists($keyname, $params))) {
                // Include key in params
                $key = $params[$keyname];
            } else if (is_array($hint) && (array_key_exists($keyname, $hint))) {
                // Include key in hint
                $key = $hint[$keyname];
            }
            return $key;
        }
    }


Before / After trigger
-----------------------------------------

* Original logic can implement back and forth by defined method of callSessionBefore and callSessionAfter.
* Override trigger methods.

callSessionBefore
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

* Execute callSessionBefore before calling the function.
* In case return value is not null, the value is returned to the call side as the return value of the method.
* In case return value is null, processing is continued and original method processing (data access) is performed.

 :: 

    // {{{ callSessionBefore
    /**
     *  @param   string  Method name
     *  @param   array   Args array for method
     *  @return  mixed   result value or null
     */
    public /** mixed */
        function callSessionBefore(/* string */ $method,
                                   /* array  */ $args)


callSessionAfter
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

* The trigger is performed after a method call is set up. 
* $result cannot be rewritten. 

  ::

    // {{{ callSessionAfter
    /**
     *  @param   string  Method name
     *  @param   array   Args array for method
     *  @param   mixed   Result value
     */
    public /* void */
        function callSessionAfter(/* string */ $method,
                                  /* array  */ $args,
                                  /* mixed  */ $result)


example) Read through cache

DataFormat(MySQL) :: 

	class Service_Cascade_DataFormat_User extends Cascade_DB_SQL_DataFormat
	{
	    // ------------------------ Attributes ---------------------------
	    // @var string  Master DSN
	    protected $master_dsn        = 'gree://master/user';
	    // @var string  Slave DSN
	    protected $slave_dsn         = 'gree://slave/user';
	    // @var array   Extra DSN list
	    protected $extra_dsn         = array();
	    // @var mixed   Primary key
	    protected $primary_key       = 'user_id';
	    // @var mixed   Data fetch key
	    protected $fetch_key         = NULL;
	    // @var boolean AUTO_INCREMENT flag
	    protected $auto_increment    = false;
	    // @var string  Last update time colomn name
	    protected $updated_at_column = 'mtime';
	    // @var string  Create time colomn name
	    protected $created_at_column = 'ctime';
	    // @var string  Table name
	    protected $table_name        = 'user';
	    // @var array   Field name list
	    protected $field_names       = array(
	        'user_id',      // User ID
	        'name',         // Name
	        'age',          // Age
	        'state',        // Status
	        'profile',      // Profile
	        'mtime',        // last update time
	        'ctime',        // create time
	    );
	    // @var array   query definitions
	    protected $queries = array(
	        'update_age' => array(
	            'sql' => 'UPDATE __TABLE_NAME__ SET age=:age WHERE user_id=:user_id',
	        ),
	        'find_by_status' => array(
	            'sql' => 'SELECT * FROM __TABLE_NAME__ WHERE state=:state',
	        ),
	    );

	    // ---------------------------------------------------------------
	}

DataFormat(memcached) :: 

	class Service_Cascade_DataFormat_Cache_User extends Cascade_DB_KVS_DataFormat
	{
	    // @var string DSN
	    protected $dsn          = 'gree(memcache)://node/user';
	    // @var int    Driver type
	    protected $driver_type  = self::DRIVER_LIBMEMCACHED;
	    // @var string  namespace
	    protected $namespace    = 'service#user';
	    // @var boolean The data compressed flag
	    protected $compressed   = true;
	};

Gateway :: 

	class Service_Cascade_Gateway_User extends Cascade_Proxy_CustomGateway
	{
	    protected $expiretime   = 1800; // memcached expire time : default: 30min

	    // {{{ callSessionBefore
	    /**
	     *  Trigger definition before facade call
	     *
	     *  @param   string  method name
	     *  @param   array   method argment values
	     *  @return  mixed   result value or null
	     */
	    public /** mixed */
	        function callSessionBefore(/* string */ $method,
	                                   /* array  */ $args)
	    {
	        try {
	            switch ($method) {
	            case 'get':
					// Get value from cache
	                $cache_session = $this->_getCacheSession();
	                if ($cache_session !== null) {
	                    list($value, $token) = $cache_session->get($args[1]/* key */);
	                    if ($value !== false) {
	                        // hit cache
	                        return $value;
	                    }
	                }
	                break;
	            case 'execute':
					// Clear cache data
			        $df = Cascade::getDataFormat($this->schema_name);
			        $keyname = $df->getCardinalKey();	// find key name
			        $key = false;
					if (is_string($keyname)) {
						// Find cache key
			            $key = $this->_searchKey($keyname, $params, $hint);
			        }

			        if ($key) {
		                $cache_session = $this->getCacheSession();
		                if ($cache_session !== null) {
		                    $cache_session->delete($key);
		                }
			        } else {
			            trigger_error(__METHOD__." : undefined key");
			        }
	                break;
	            default:
	                break;
	            }
	        } catch (Exception $e) {
	            // Ignore Exception
	            trigger_error(__METHOD__." : catch exception : " . $e->getMessage());
	        }
	        return null;
	    }
	    // }}}
	    // {{{ callSessionAfter
	    /**
	     *  Trigger definition after facade call
	     *
	     *  @param   string  method name
	     *  @param   array   method argment values
	     *  @param   mixed   result value
	     */
	    public /* void */
	        function callSessionAfter(/* string */ $method,
	                                  /* array  */ $args,
	                                  /* mixed  */ $result)
	    {
	        try {
	            switch ($method) {
	            case 'get':
					// Set result to cache
	                $cache_session = $this->_getCacheSession();
	                if ( ($cache_session !== null) && ($result) ) {
	                    $cache_session->set($args[1]/* key */, $result, $this->expiretime);
	                }
	                break;
	            default:
	                break;
	            }
	        } catch (Exception $e) {
	            // Ignore Exception
	            trigger_error(__METHOD__." : catch exception : " . $e->getMessage());
	        }
	        return;
	    }
	    // }}}

	    // Get cache session
	    private function _getCacheSession()
	    {
	        $schema_name = $this->namespace . '#cache_' . $this->identifier;
	        $cache_session = null;
	        $cache_session = Cascade::getAccessor($schema_name);
	        return $cache_session;
	    }

	    // Search key from paramator and hint
	    private function _searchKey($keyname, $params=null, $hint=null)
	    {
	        $key = false;
	        if (is_array($params) && (array_key_exists($keyname, $params))) {
	            $key = $params[$keyname];
	        } else if (is_array($hint) && (array_key_exists($keyname, $hint))) {
	            $key = $hint[$keyname];
	        }
	        return $key;
	    }
	}



Processed in order of the following.

#. callSessionBefore
#. < DATA ACCESS >
#. callSessionAfter


Original method
-----------------------------------------

example) Method which increases a friend : Insert link information and update the number of friends. 


DataFormat(user infomation) :: 

	class Service_Cascade_DataFormat_User extends Cascade_DB_SQL_DataFormat
	{
	    // ------------------------ Attributes ---------------------------
	    // @var string  Master DSN
	    protected $master_dsn        = 'gree://master/user';
	    // @var string  Slave DSN
	    protected $slave_dsn         = 'gree://slave/user';
	    // @var array   xtra DSN list
	    protected $extra_dsn         = array();
	    // @var mixed   Primary key
	    protected $primary_key       = 'user_id';
	    // @var mixed   Data fetch key
	    protected $fetch_key         = NULL;
	    // @var boolean AUTO_INCREMENT flag
	    protected $auto_increment    = false;
	    // @var string  Last update time colomn name
	    protected $updated_at_column = 'mtime';
	    // @var string  Create time colomn name
	    protected $created_at_column = 'ctime';
	    // @var string  Table name
	    protected $table_name        = 'user';
	    // @var array   Field name list
	    protected $field_names       = array(
	        'user_id',      // User ID
	        'name',         // Name
	        'link_num',     // Number of friends
	        'state',        // Status
	        'profile',      // Profile
	        'mtime',        // Last update time
	        'ctime',        // Created time
	    );
	    // @var array   query definitions
	    protected $queries = array(
	        'increment_link_num' => array(
	            'sql' => 'UPDATE __TABLE_NAME__ SET link_num=link_num+1 WHERE user_id=:user_id',
	        ),
	    );

	    // ---------------------------------------------------------------
	}

DataFormat(user link infomation) :: 

	class Service_Cascade_DataFormat_Friend extends Cascade_DB_SQL_DataFormat
	{
	    // ------------------------ Attributes ---------------------------
	    // @var string  Master DSN
	    protected $master_dsn        = 'gree://master/friend';
	    // @var string  Slave DSN
	    protected $slave_dsn         = 'gree://slave/friend';
	    // @var array   Extra DSN list
	    protected $extra_dsn         = array();
	    // @var mixed   Primary key
	    protected $primary_key       = array('user_id', 'to_user_id');
	    // @var mixed   Data fetch key
	    protected $fetch_key         = NULL;
	    // @var boolean AUTO_INCREMENT flag
	    protected $auto_increment    = false;
	    // @var string  Last update time colomn name
	    protected $updated_at_column = 'mtime';
	    // @var string  Create time colomn name
	    protected $created_at_column = 'ctime';
	    // @var string  Table name
	    protected $table_name        = 'friend';
	    // @var array   Field name list
	    protected $field_names       = array(
	        'user_id',      // User ID
	        'to_user_id',   // Friend user ID
	        'mtime',        // Last update time
	        'ctime',        // Created time
	    );
	    // @var array   query definitions
	    protected $queries = array(
	        'create' => array(
	            'sql' => 'INSERT INTO __TABLE_NAME__ (user_id, to_user_id, ctime) VALUES (:user_id, :to_user_id, NOW()), (:to_user_id, :user_id, NOW())',
	        ),
	    );

	    // ---------------------------------------------------------------
	}

Gateway ::

	class Service_Cascade_Gateway_Friend extends Cascade_Proxy_CustomGateway
	{
	    public function addFriend($user_id, $to_user_id)
	    {
	        // CAUTION: It omits, although there must originally be a prior application or there must be a number check of the maximum friends. 

	        // Get user session
	        $schema_name = $this->namespace . '#user';
	        $user_session = Cascade::getAccessor($schema_name);
	        if ($user_session == NULL) {
	            throw new Service_Exception(__METHOD__ . " invalid schema : " . $schema_name);
	        }
	        // Count up number of friend
	        $result = $user_session->execute('increment_link_num', array('user_id' => $user_id));
	        if ($result == 0) {
	            throw new Service_Exception(__METHOD__ . " increment_link_num failed : " . $user_id));
	        }
	        $result = $user_session->execute('increment_link_num', array('user_id' => $to_user_id));
	        if ($result == 0) {
	            // CATION: Error-handling abbreviation
	            throw new Service_Exception(__METHOD__ . " increment_link_num failed : " . $to_user_id));
	        }

			// Insert friend link data
	        $params = array(
	            'user_id'       => $user_id,
	            'to_user_id'    => $to_user_id,
	        );
	        $result = $this->session->execute('create', $params);
	        if ($result == 0) {
	            // CATION: Error-handling abbreviation
	            throw new Service_Exception(__METHOD__ . " create failed : " . $user_id . ", " . $to_user_id));
	        }
	    }

	}


usage :: 

	try {
	    $cascade = Cascade::getAccessor('service#friend');
	    // Add friend (Original method)
	    $cascade->addFriend($user_id, $to_user_id);

	    // Get friend data
	    $params = array('user_id'=>$user_id, 'to_user_id'=>$to_user_id);
	    $friend = $cascade->get($params);

	} catch (Exception $e) {
	    throw $e;
	}



