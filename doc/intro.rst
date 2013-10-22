Introduction
==============================
Cascade is GREE's data access library for PHP
which offers an unified interface for accessing various kind of backends such as:

* databases
* files
* caching systems

Supported backends
------------------------------
* SQL mode

  * MySQL, etc...

* KVS mode

  * Memcache, Flare, Squall, APC, EAC, etc...

* Config file

  * PHPAraay, CSVFile, IniFile, etc...

* Gateway

  * PassThroughGateway
  * CustomGateway (with WriteThroughCach, WriteBackCache, WriteDelayCache)

Design
------------------------------
* Simple data access
* Extendibility


Data access example::

  require_once 'Cascade.php'

  $ac = Cascade::getAccessor('sample#item')

  // case of SQL DataFormat
  $result = $ac->get($id);
  $result = $ac->execute('delete', $params, $offset, $limit);

  // case of KVS DataFormat
  $result = $ac->get($key);
  $ac->set($key, $val);

  // case of Config file DataFormat
  $result = $ac->get($section);

  // case of Extended DataFormat(possible to add)
  $ac->xxxxxx($section);

Architecture
------------------------------
.. image:: images/Cascade-summary.png
