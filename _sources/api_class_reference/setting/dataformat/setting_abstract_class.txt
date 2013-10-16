.. _setting_dataformat_abstract_class:

Abstract Class
=========================================================================================================
This class is defined configurations of configuration file informations

Class synopsis
---------------------------------------------------------------------------------------------------------

| abstract class Cascade_DB_Config_DataFormat
|     extends    Cascade_DB_DataFormat
| {
|     **// Class Constants**
|     const DRIVER_PHPARAY = Cascade::DRIVER_PHPARRAY;
|     const DRIVER_INIFILE = Cascade::DRIVER_INIFILE;
|     const DRIVER_CSVFILE = Cascade::DRIVER_CSVFILE;
|     const FETCH_MODE_NUM = Cascade_DB_Config_Statement::FETCH_MODE_NUM;
|     const FETCH_MODE_ASSOC = Cascade_DB_Config_Statement::FETCH_MODE_ASSOC;
|
|     **// Properties**
|     protected *$config_path* = ``NULL``;
|     protected *$config_file* = ``NULL``;
|     protected *$driver_type* = self::DRIVER_INIFILE;
|     protected *$fetch_mode* = self::FETCH_MODE_ASSOC;
|     protected *$interceptors* = array();
|
|     **// Methods**
|     public string `getConfigFilePath <./methods/getconfigfilepath.html>`__ ( void )
|     final public int `getDriverType <./methods/getdrivertype.html>`__ ( void )
|     final public int `getFetchMode <./methods/getfetchmode.html>`__ ( void )
| }


Class variables
---------------------------------------------------------------------------------------------------------

$config_path
 type:
  string
 description:
  PATH of configuration files
 detail:
  Sets the directory path where configuration files are located.
 example:
  | $config_path = '/path/to/config/derectory'

---------------------------------------------------------------------------------------------------------

$config_file
 type:
  string
 description:
  configuration file
 detail:
  Sets the configuration file name
 example:
  | $config_path = 'service_config.ini'

---------------------------------------------------------------------------------------------------------

$driver_type
 type:
  int
 description:
  type of configuration file
 detail:
  Sets the configuration file type. See also "ConfileFile driver types" below.
 example:
  | $config_path = self::DRIVER_INIFILE

---------------------------------------------------------------------------------------------------------

$fetch_mode
 type:
  int
 description:
  Fetch mode of results
 detail:
  Sets the fetch mode of resutls. See aslo "Fetch mode of results" below. Normally, set FETCH_MODE_ASSOC
 example:
  | $fetch_mode = self::FETCH_MODE_ASSOC

---------------------------------------------------------------------------------------------------------

Class Constants

---------------------------------------------------------------------------------------------------------

.. list-table:: ConfigFile driver types
   :header-rows: 1

 * - Constants
   - Description
 * - Cascade_DB_Config_DataFormat::DRIVER_PHPARRAY
   - PHP Array file
 * - Cascade_DB_Config_DataFormat::DRIVER_INIFILE
   - ini file
 * - Cascade_DB_Config_DataFormat::DRIVER_CSVFILE
   - CSV file

.. list-table:: Fetch mode of results
   :header-rows: 1

 * - Constants
   - Description
 * - Cascade_DB_Config_DataFormat::FETCH_MODE_NUM
   - index array
 * - Cascade_DB_Config_DataFormat::FETCH_MODE_ASSOC
   - associative array

















$driver_type
 type:
  int
 description:
  identifier of connection driver
 detail:
  Specify the driver userd to query the databse to KVS. See also to class constant below.
 example:
  | $driver_type = self::DRIVER_LIBMEMCACHED

---------------------------------------------------------------------------------------------------------

$namespace
 type:
  string
 description:
  namespace
 detail:
  Namespace is used as a prefix to avoid conflicts for key. For example, $namespace = 'foo', $key = 'bar' as it is actually stored in 'foo#bar'. The use of namespaces, allowing coexistence of multiple DataFormat in same KVS server.
 example:
  | $namespace = 'user'

---------------------------------------------------------------------------------------------------------

$compressed
 type:
  boolean
 description:
  flag of compression
 detail:
  If you want to disable compression, set this flag to false. When using 'increment' or 'decrement', you must set to false.
 example:
  | $compressed = true

Class Constants
---------------------------------------------------------------------------------------------------------

.. list-table::
  :header-rows: 1

 * - Variable
   - Description
 * - Cascade_DB_KVS_DataFormat::DRIVER_EAC
   - eAccelerator
 * - Cascade_DB_KVS_DataFormat::DRIVER_APC
   - APC
 * - Cascade_DB_KVS_DataFormat::DRIVER_MEMCACHED
   - Memcached
 * - Cascade_DB_KVS_DataFormat::DRIVER_FLARE
   - flare
