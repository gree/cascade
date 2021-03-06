.. _kvs_dataformat_abstract_class:

Abstract Class
=========================================================================================================
This class is defined configurations of KVS Table informations

Class synopsis
---------------------------------------------------------------------------------------------------------

| abstract class Cascade_DB_KVS_DataFormat
|     extends    Cascade_DB_DataFormat
| {
|     **// Class Constants**
|     const DRIVER_EAC = Cascade::DRIVER_EAC;
|     const DRIVER_APC = Cascade::DRIVER_APC;
|     const DRIVER_MEMCACHED = Cascade::DRIVER_MEMCACHED;
|     const DRIVER_LIBMEMCACHED = Cascade::DRIVER_LIBMEMCACHED;
|     const DRIVER_FLARE = Cascade::DRIVER_FLARE;
|     const DRIVER_SQUALL = Cascade::DRIVER_SQUALL;
|
|     **// Properties**
|     protected *$dsn* = ``NULL``;
|     protected *$driver_type* = self::DRIVER_KVS_FLARE;
|     protected *$namespace* = ``NULL``;
|     protected *$compressed* = ``TRUE``;
|     protected *$interceptors* = array(
|       'Cascade_AOP_KVS_StatementCacheInterceptor',
|     );
|
|     **// Methods**
|     final string public `getDSN <./methods/getdsn.html>`__ ( Cascade_DB_Criteria *$criteria* )
|     final int public `getDriverType <./methods/getdrivertype.html>`__ ( void )
|     public string `getNamespace <./methods/getnamespace.html>`__ ( void )
|     final public boolean `isCompressed <./methods/iscompressed.html>`__ ( void )
|     final public `getExpiration <./methods/getexpiration.html>`__ ( void )
| }


Class variables
---------------------------------------------------------------------------------------------------------

$dsn
 type:
  string
 description:
  DSN
 detail:
  selected by default DSN
 example:
  | $dsn = 'gree(memcache)://node/user'

---------------------------------------------------------------------------------------------------------

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
