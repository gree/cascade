.. _sql_function_getEx:

getEx
=========================================================================================================
Data is retrieved by the specified key

Description
---------------------------------------------------------------------------------------------------------
.. list-table:: 

  * - mixed **getEx** (string *$extra_dsn*, string *$key*\[, mixed *$hint* = ``null``, boolean *$use_master* = ``false``\])

**getEx** returns results of data from specified key. You can specify extended DSN.

Parameters
---------------------------------------------------------------------------------------------------------
* **extra_dsn**

  * extended DSN

* **keys**

  * key value list that is the condition

* **hint**

  * hint of FarmSelect

* **use_master**

  * flag of connect to master

Return Values
---------------------------------------------------------------------------------------------------------


Examples
---------------------------------------------------------------------------------------------------------

