.. _sql_dataformat_methods:

Cascade_DB_SQL_DataFormat::getFetchKey
=========================================================================================================
Get the key of data acquisition

Description
---------------------------------------------------------------------------------------------------------
.. list-table::

 * - mixed **getFetchKey** ( void )

To get the key will override the primary key data at the time of acquisition. The case of a composite key, to build more than one column name in the form of an array. Functions that effects Cascade_DB_SQL_Session::get(), Cascade_DB_SQL_Session::getEx(), Cascade_DB_SQL_Session::mget(), Cascade_DB_SQL_Session::mgetEx()

Parameters
---------------------------------------------------------------------------------------------------------
* **void**

Return Values
---------------------------------------------------------------------------------------------------------
* **string|array**

 * Key data acquisition
