.. _sql_dataformat_methods:

Cascade_DB_SQL_DataFormat::getTableName
=========================================================================================================
Get talbe name

Description
---------------------------------------------------------------------------------------------------------
.. list-table::

 * - string **getTableName** ( Cascade_DB_Criteria *$criteria* )

To get the value to replace the reserved word table name in the query string. If you want to dynamically retrieves the information table, it returns the name of the table based on criteria.

Parameters
---------------------------------------------------------------------------------------------------------
* **criteria**

 * Extraction condition

Return Values
---------------------------------------------------------------------------------------------------------
* **string**

 * table name
