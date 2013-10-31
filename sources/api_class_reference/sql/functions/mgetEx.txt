.. _sql_function_mgetEx:

mgetEx
=========================================================================================================
Data is retrieved by the specified key list

Description
---------------------------------------------------------------------------------------------------------
.. list-table:: 

  * - mixed **mgetEx** (string *$extra_dns*, array *$keys*\[, mixed *$hint* = ``null``, boolean *$use_master* = ``false``\])

**mgetEx** returns results of data from specified key list. You can specify extended DSN.

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

