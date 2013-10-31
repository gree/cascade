.. _sql_function_findex:

findEx
=========================================================================================================
List of data is retrieved by the specified key

Description
---------------------------------------------------------------------------------------------------------
.. list-table:: 

  * - mixed **findEx** (string *extra_dsn*, string *$stmt_name*, array *$params*\[, int *$offset* = ``null``, int *$limit* = ``null``, mixed *$hint* = ``null``, boolean *$use_master* = ``false``\])

**find** returns results of data list from specified key

Parameters
---------------------------------------------------------------------------------------------------------
* **extra_dsn**

  * name of extended DSN

* **stmt_name**

  * name of statement

* **params**

  * parameters of condition

* **offset**

  * starting row position of data retrieving

* **limit**

  * limitting rows of results returned

* **hint**

  * hint of FarmSelect

* **use_master**

  * flag of connect to master

Return Values
---------------------------------------------------------------------------------------------------------


Examples
---------------------------------------------------------------------------------------------------------

