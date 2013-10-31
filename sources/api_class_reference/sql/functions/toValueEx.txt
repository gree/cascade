.. _sql_function_toValueEx:

toValueEx
=========================================================================================================
Retrieving a single data from the specified condition

Description
---------------------------------------------------------------------------------------------------------
.. list-table::

  * - mixed **toValueEx** (string *$extra_dsn*, string *$stmt_name*, array *$params*\[, mixed *$hint* = ``null``, boolean *$use_master* = ``false``\])

**toValueEx** returns result of a single data from specified condition. You can specified extended DSN.

Parameters
---------------------------------------------------------------------------------------------------------
* **extra_dsn**

  * extended DSN

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

