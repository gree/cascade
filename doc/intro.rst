Introduction
==============================
Cascade

* provides a simple interface for interacting with various storage backends
* allows complex transactions to be handled through a simple interface

Features
------------------------------
* SQL backends

  * For usage with MySQL, etc...

* KVS backends

  * For usage with Memcache, Flare, APC, EAC, etc.

* Config files

  * Read values from config files written in PHP, CSV, ini, etc.

Design
------------------------------
* Simple interface
* Extensibility

Data access example::

  require_once 'Cascade.php'

  $ac = Cascade::getAccessor('sample#item')

  // SQL DataFormat
  $result = $ac->get($id);
  $result = $ac->execute('delete', $params, $offset, $limit);

  // KVS DataFormat
  $result = $ac->get($key);
  $ac->set($key, $val);

  // Config file DataFormat
  $result = $ac->get($section);

  // Custom DataFormat (your own custom implementation)
  $ac->xxxxxx($section);

Architecture
------------------------------
.. image:: images/Cascade-summary.png
