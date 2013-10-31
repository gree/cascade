.. _sql_function_findFirstEx:

findFirstEx
=========================================================================================================
Data is retrieved by the specified key

Description
---------------------------------------------------------------------------------------------------------
.. list-table:: 

  * - mixed **findFirstEx** (string *$extra_dsn*, string *$stmt_name*, array *$params*\[, mixed *$hint* = ``null``, boolean *$use_master* = ``false``\])

**findFirstEx** returns results of data from specified key. You can specify extended DSN.

Parameters
---------------------------------------------------------------------------------------------------------
* **extra_dsn**

  * name of extended DSN

* **stmt_name**

  * name of statement

* **params**

  * parameters of condition

* **hint**

  * hint of FarmSelect

* **use_master**

  * flag of connect to master

Return Values
---------------------------------------------------------------------------------------------------------


Examples
---------------------------------------------------------------------------------------------------------

