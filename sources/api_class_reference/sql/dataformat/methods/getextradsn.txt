.. _sql_dataformat_methods:

Cascade_DB_SQL_DataFormat::getExtraDSN
=========================================================================================================
Get extra DSN

Description
---------------------------------------------------------------------------------------------------------
.. list-table::

 * - string **getExtraDSN** ( Cascade_DB_Criteria *$criteria* )

Master DSN, DSN is the only slaves, used to be difficult to scale to. Determination of the DSN extension is carried out from the extraction conditions.

Parameters
---------------------------------------------------------------------------------------------------------
* **criteria**

 * Extraction condition

Return Values
---------------------------------------------------------------------------------------------------------
* **string**

 * slave DSN
