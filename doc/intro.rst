Introduction
==============================
Cascade is:

* offering the integrated interface without considering of any kind of storages.
* concealing the logic of data access into the inside.

Supported functions
------------------------------
* SQL mode

  * MySQL, etc...

* KVS mode

  * Memcache, Flare, Squall, APC, EAC, etc...

* Config file

  * PHPArray, CSVFile, IniFile, etc...

* Gateway

  * PassThroughGateway
  * CustomGateway (with WriteThroughCach, WriteBackCache, WriteDelayCache)

Design
------------------------------
* Simple access
* Extensibility


Data access sample::

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
