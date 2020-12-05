=======================
Pomm-project Foundation
=======================

.. contents::

Overview
--------

Foundation is a light, fast and versatile PHP PostgreSQL database framework. It can either be used on its own (to replace a DBAL for example) or to build a more complex `model manager`_. It is the first stone of `Pomm project`_ version 2.

..  _`Pomm project`: http://www.pomm-project.org
..  _`model manager`: https://github.com/pomm-project/ModelManager

Foundation manages relations between a database *connection* and *clients* through *sessions*. It consists of several classes to configure, open, deal with and close *sessions*.

- ``Pomm`` is the service class, it registers *session builders* and caches spawned *sessions*.
- ``SessionBuilder`` configures and builds *sessions*.
- ``Session`` holds *clients* and *poolers* and the *connection*.
- ``Client`` abstract class that implements ``ClientInterface``. Instances are *session*’s *clients*.
- ``ClientPooler`` abstract class that implements ``ClientPoolerInterface``. They manage *clients* in *sessions*.

This complexity is at first glance hidden. If one wants to open a connection, send a query and get converted results, it is as simple as:

.. code:: php

    <?php
    //…

    $pomm = new Pomm(['my_database' => ['dsn' => 'pgsql://user:pass@host:port/db_name']]);

    $result = $pomm['my_database']  // ← note 1
        ->getQueryManager()         // ← note 2
        ->query('select * from my_table where my_field = $*', ['some value'])
        ;                           // ↑ note 3

    if ($result->isEmpty()) {
        printf("There are no results with the given parameter.\n");
    } else {
        foreach ($result as $row) { // ← note 4
            printf(
                "field1 = '%s', field2 = '%s'.\n",
                $row['field1'],     // ← note 5
                $row['field2'] === true ? 'OK' : 'NO'
            );
        }
    }

:note 1:
    This returns a *session* using a *session builder*.

:note 2:
    This returns the default ``query_manager`` *client* using the ``QueryManagerPooler``.

:note 3:
    Issue the parametrized query and return a ``ConvertedResultIterator``.

:note 4:
    Traverse the result as an array and fetch rows.

:note 5:
    Access field result. Those results are converted to a PHP equivalent type (see `Converter pooler`_).

Pomm service
------------

Pomm service is an interface to easily declare and build *sessions* through *session builders*.

Using session builders
~~~~~~~~~~~~~~~~~~~~~~

It is possible to declare session builders either using ``Pomm``’s class constructor or the ``addBuilder`` method:

.. code:: php

    <?php

    $pomm = new Pomm(['first_db' => ['dsn' => 'pgsql://user:pass@host/first_db']]);
    $pomm->addBuilder('second_db', new MySessionBuilder(['dsn' => 'pgsql://user:pass@host/second_db']));

It is often more practical to declare all *sessions* configuration from the constructor directly even if the builder is a custom class:

.. code:: php

    <?php

    $pomm = new Pomm(
        [
            'first_db' =>
                [
                    'dsn' =>  'pgsql://user:pass@host/first_db'
                ],
            'second_db' =>
                [
                    'dsn' => 'pgsql://user:pass@host/second_db',
                    'class:session_builder' => '\Project\MySessionBuilder',
                    'pomm:default' => true,
                ]
        ]
    );

Each session builder has a name. This name is important, it represents a configuration and is not coupled with the DSN. This is particularly useful when an application has to switch from a database to another with the same configuration.

Spawning sessions
~~~~~~~~~~~~~~~~~

The easiest way to get a session from the *service* is to use the ``ArrayAccess`` implementation:

.. code:: php

    <?php

    $session = $pomm['first_db'];

    // this is strictly equivalent to

    $session = $pomm->getSession('first_db');

The ``getSession($name)`` method checks if a *session* using this *session builder* has already been created. If yes, it is returned, otherwise a new one is created using the ``createSession($name)``. This last method creates a new session every time it is called. This implies a new database connection will be used.

Default sessions
~~~~~~~~~~~~~~~~

Sometimes session names are not that important (especially if there is only one session), in this case it is possible to use Pomm’s default session mechanism. It will use the first first declared one:

.. code:: php

    <?php

    $pomm = new Pomm(
        [
            'first_db' =>
                [
                    'dsn' =>  'pgsql://user:pass@host/first_db'
                ],
        ]
    );

    $session = $pomm->getDefaultSession(); // return a `first_db` session

This still applies when several session builders are declared. It is still possible to explicitly declare a session builder as being the default one by setting the ``pomm::default`` configuration setting to true.

Context dependent configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Session builders do configure session but in some cases, configuration options may be context dependent like development options or production options. This kind of configuration occurs directly in Pomm service passing anonymous functions:

.. code:: php

    <?php
    // …
    $pomm->addPostConfiguration('first_db', function($session) { /* … */ });

When the session is created, the post-configuration functions are launched and the session is returned.

Session builders management
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Pomm provides several methods to manage session builders:

- ``addBuilder($builder_name, VanillaSessionBuilder $builder)``
- ``hasBuilder($name)``
- ``removeBuilder($name)``
- ``getBuilder($name)``
- ``getSessionBuilders()``

Session builder
---------------

*Session builders* are meant to configure and instantiate *sessions*. It is possible to use them on their own without ``Pomm`` *service*.

.. code:: php

    use PommProject\Foundation\Session\SessionBuilder;

    $session = (new SessionBuilder(['dsn' => 'pgsql://user:pass@host/db_name']))
        ->buildSession()
        ;

The session builder shown above creates blank sessions with no poolers registered. Foundation provides a functional builder with all poolers registered and a dedicated session class:

.. code:: php

    use PommProject\Foundation\SessionBuilder; // ← different session builder

    $session = (new SessionBuilder(['dsn' => 'pgsql://user:pass@host/db_name']))
        ->buildSession()
        ;

Configuration
~~~~~~~~~~~~~

There are several ways to set the configuration:

.. code:: php

    <?php

    $session_builder = new SessionBuilder(
        [
            'dsn'   => 'pgsql://user:pass@host:port/db_name',
            'param' => 'value',
        ]
    );
    $session_builder->addParameter('my_parameter', 'my_value');

In a more general way, ``SessionBuilder`` class is made to be extended by a project-dedicated *session builder* class. It is then possible to overload the ``getDefaultConfiguration()`` method. It keeps the class configurable with a custom default configuration.

Configuration options
~~~~~~~~~~~~~~~~~~~~~

The ``dsn`` is the only mandatory parameter expected by the builder but more parameters can be passed:

- ``connection:configuration`` (array)
- ``dsn`` (string) mandatory
- ``class:session`` (string) default:  ``\PommProject\Foundation\Session\Session``
- ``connection:persist`` (bool) default: ``false``

The ``connection:configuration`` parameter contains a hashmap of postgresql settings (see `postgresql documentation <http://www.postgresql.org/docs/9.1/static/runtime-config-client.html>`_). The default settings are the following:

- ``bytea_output``                (string) default: ``hex``
- ``intervalstyle``               (string) default: ``ISO_8601``
- ``datestyle``                   (string) default: ``ISO``
- ``standard_conforming_strings`` (string) default: ``true``

The ``connection:persist`` parameter allows persistent connections to the database (see `pg_connect() documentation <https://www.php.net/manual/en/function.pg-pconnect.php>`_). The default setting is to not use persistent connections.  Before enabling this setting, please be sure that you want this option turned on and know the caveats it brings.

**dsn** is the only mandatory parameter, it is used to connect to the Postgresql database. The syntax is the following::

    pgsql://user:password@host:port/db_name

Examples::

    pgsql://db_user/db_name
    pgsql://db_user:p4sS@192.168.1.101/db_name
    pgsql://db_user:p4sS@192.168.1.101:5433/db_name
    pgsql://db_user@!/var/run/postgres!:5433/db_name


:Note:
    The Pgsql library is sensible to environment variables ``PGHOST`` ``PGPORT`` (see `the documentation <http://www.postgresql.org/docs/9.1/static/libpq-envars.html>`_). When using PHP from the command line (or the built-in web server), theses variables will have an impact if they are not overridden by some of the DSN’s parameters.

:Note:
    The host part may be a path on the local file system surrounded by the ``!`` character. When this is the case, the Unix socket present in the given directory is used to connect to the database.

Session customization
~~~~~~~~~~~~~~~~~~~~~

The ``SessionBuilder`` class is made to be extended. Foundation package incidentally proposes two *session builders*:

- ``PommProject\Foundation\Session\SessionBuilder`` blank session builder.
- ``PommProject\Foundation\SessionBuilder`` builder with Foundation *clients* and *poolers* loaded and configured.

It is encouraged to create a project-dedicated *session builder* that extends one of these classes. Several methods are available to change a *session builder* behavior:

:``getDefaultConfiguration``:
    Overrides default configuration. The core default configuration is the `connection:configuration` parameter. Be aware it will break the default converter system if discarded.

:``preConfigure()``:
    Change the configuration just before a session is instantiated.

:``postConfigure($session)``:
    Place where default *session poolers* and *clients* are registered into a brand new *session*.

:``createSession()``:
    If a custom session class is to be instantiated.

:``createClientHolder()``:
    If a custom *session holder* is to be used from within the *session*.

:``initializeConverterHolder()``:
    Customize the *converter holder*. Remember all *sessions* created by the builder will have this converter holder whatever their DSN.

:``createConnection()``:
    How to create a ``Connection`` instance based on the configuration.



Converter holder
~~~~~~~~~~~~~~~~

The *converter holder* is a special configuration setting. It holds all the converters and is cloned when passed as parameter to the `converter pooler`_. A pre-configured customized *converter holder* can be passed as parameter to the *session builder*’s constructor:

.. code:: php

    <?php

    $session_builder = new SessionBuilder(
        ['dsn' => 'pgsql://user:pass@host:port/db_name'],
        new MyConverterHolder()
        );

The ``initializeConverterHolder()`` method is used internally to register default PostgreSQL types converters, use it to add your own default converters. The ``ConverterHolder`` instance is passed as reference. Remember, this converter holder will be used for **all** sessions created by the builder whatever their DSN. If a database specific converter is to be registered, the best place for it might be the ``postConfigure`` method, dealing directly with the `converter pooler`_.

Session
-------

*Session* is the keystone of the Foundation package. It provides a *connection* API to *clients*. To be able to do this, *clients* must register to the *session* using the ``registerClient(ClientInterface)`` method. The *session* adds the *client* in the *client pool*. In exchange, it injects itself in the *client* using the ``initialize(Session)`` method (see `Client`_). Starting from this, the *client* can use the *connection* and other *clients*.

*Clients* are accessed using the ``getClient($type, $identifier)`` method. If no clients match the corresponding type and identifier, ``null`` is returned. This can be a problem because the Client must then be instantiated and registered to the Session. This is the role of the *client poolers* (aka *poolers*). *Poolers* are, in a way, *clients* manager for a given type. Not all types need a *pooler*, for example, the ``fixture`` clients type manage database test structures and data. They are here to create tables and types needed by tests on startup and to drop them on shutdown. Alternatively, the `prepared query pooler`_ takes the SQL query as client identifier. If the given query has already been performed, it is re used. Otherwise, a new statement is prepared and then executed. When the *connection* goes down, all statements are deallocated.

Some *clients* may use *clients* from different types using their respective *poolers*. For example, the ``PreparedQueryManager`` *client* uses the `query manager pooler`_ and then the `converter pooler`_.

There are several ways to access *clients* and *poolers* using the *session*:

:``getClient($type, $identifier)``:     return the asked *client* if it exists, null otherwise.
:``getClientUsingPooler($type, $identifier)``:  ask for a *client* using a *client pooler*.

There is a shortcut for the last method:

.. code:: php

    <?php

    $client = $session->getType($identifier);

    // strictly equivalent to
    $client = $session->getClientUsingPooler($type, $identifier);

    // which is the same as
    $client = $session
        ->getPoolerForType($type)
        ->getClient($identifier)
        ;

Client
------

A *client* is a bit of work with the database. They should be as simple as possible and as reliable as possible. They work together through *session* and *poolers*.

All *clients* must implement ``ClientInterface``. Because a part of this implementation is always the same, it is possible to either extend ``PommProject\Foundation\Client\Client`` or to use ``PommProject\Foundation\Client\ClientTrait``. (The ``Client`` abstract class just uses the ``ClientTrait``). The interface defines 4 methods to be implemented:

:``getClientType()``:   Return client type, not implemented in ``ClientTrait``.
:``getClientIdentifier()``:  Return client identifier, not implemented in ``ClientTrait``.
:``initialize(Session)``:   When the client is registered by the session, the session injects itself in the *client* using this method.
:``shutdown()``:    If things are to be done before connection is going down.

Client pooler
-------------

A *client pooler* manages *clients* of a given type. Its role is to return a client or throw an exception otherwise.

All *client poolers* must implement ``ClientPoolerInterface``. It is possible to easily implement this either by extending ``ClientPooler`` or using ``ClientPoolerTrait`` (the abstract class uses the trait). The interface defines three methods:

:``getPoolerType()``:   Return the type of *clients* managed by this *pooler*, not implemented in ``ClientPoolerTrait``.
:``register(Session)``:  When the *pooler* is registered to the session, the session injects itself in the *pooler* using this method.
:``getClient($identifier)``:    Method called to fetch a *client* using this *pooler*.

Because most *poolers* behave the same way, the ``ClientPoolerTrait`` add methods to work like the following. When a *client* is requested:

#. Retrieve the client from the *session*’s *client holder*.
#. If null is returned, it launches ``createClient($identifier)`` method.
#. If the *client* cannot be created, an exception must be thrown.
#. Return the *client*.

Default client poolers
----------------------

Here is a comprehensive list of the *poolers* registered by default with ``PommProject\Foundation\SessionBuilder``.

Converter pooler
~~~~~~~~~~~~~~~~

:Type:  converter

Responsible of proposing converter *clients*. If a client is not found, it checks in the *converter holder* if the given type has a converter. If yes, it wraps the *converter* in a ``ConverterClient`` and registers it to the session. There are as many ``ConverterClient`` as registered types but they can share the same *converter* instances.

This way, it is possible to add custom converters or converters for database specific types like composite types. The best place to do that is in a `Session builder`_’s ``postConfigure(Session)`` method:

.. code:: php

    <?php
    //…
    function postConfigure(Session $session)
    {
        $session
            ->getPoolerForType('converter')
            ->getConverterHolder()
            ->addTypeToConverter('my_schema.latlong', 'Point') // ← convert a domain of point
            ->registerConverter('Hstore', new PgHstore(), ['public.hstore']) // ← register Hstore converter
            ;
    }

Even though the converters coming with Foundation cover a broad range of PostgreSQL’s types, it is possible to write custom converters as long as they implement ``ConverterInterface``. Be aware that the format of the data coming from Postgres may be configuration dependent (dates, money, number etc.). Default converters fit the default configuration set in the `Session builder`_.


Inspector pooler
~~~~~~~~~~~~~~~~

:Type:  inspector

The inspector proposes methods to get information about database structure (schemas, tables, fields etc.).

This *pooler* calls the ``PommProject\Foundation\Inspector\LegacyInspector`` *client* by default. It is possible to specify another *client* class as identifier, the *pooler* will try to instantiate it.

Available *clients*:

 * `LegacyInspector` is the Pomm 2.0 inspector with the same methods.
 * `DatabaseInspector` can query the version the size and names of the databases.
 * `SchemaInspector` list schemas in a given database.
 * `RelationInsepctor` list relations with information like size, columns etc.
 * `TypeInspector` list system types or user defined types.

Listener pooler
~~~~~~~~~~~~~~~

:Type:  listener

A ``Listener`` is a class that can hold anonymous functions that are triggered when the listener receives a notification with the listener’s name.

Foundation owns a basic event dispatcher mechanism.

.. code:: php

    <?php
    //…

    $session
        ->getListener('my_event')
        ->attachAction(function($event_name, $data, $session) { // do something })
        ;

To trigger the attached functions, the listener *pooler* proposes a ``notify(array, mixed)`` method. The first argument is an array of event names and the second is the data payload to be sent. Albeit simple, this mechanism is powerful since all attached functions have access to the session hence all the *poolers*.

There is also a method to notify all clients:

.. code:: php

    <?php
    //…

    $session
        ->getPoolerForType('listener')
        ->notify('*', $some_data)
        ;

Observer pooler
~~~~~~~~~~~~~~~

:Type:  observer

Observer *pooler* aims at leveraging the ``LISTEN/NOTIFY`` mechanism in PostgreSQL. An observer *client* can be used to listen to PostgreSQL events sent with the ``NOTIFY`` SQL command. It is possible to ask the observer either to send back the event payload if any or to throw a ``NotificationException`` when a notification is caught.

Prepared query pooler
~~~~~~~~~~~~~~~~~~~~~

:Type: prepared_query

This *pooler* prepares statements if they do not already exist and executes them with parameters:

.. code:: php

    <?php
    //…
    $session
        ->getPreparedQuery('select * from my_table where some_field = $*')
        ->execute(['some_content']
        ;

It returns a ``ResultHandler`` instance with raw results. (see `Query manager pooler`_).

Query manager pooler
~~~~~~~~~~~~~~~~~~~~

:Type:  query_manager

The query manager *pooler* returns a traversable iterator (see `result iterators`_) on converted results. The default *client* is a simple parametrized query but Foundation also comes with a prepared query manager:

.. code:: php

    <?php
    //…
    $result = $session
        ->getQueryManager('\PommProject\Foundation\PreparedQuery\PreparedQueryManager')
        ->query('select * from my_table where some_field = $*', ['some_content'])
        ;

If no client class is provided, the default ``PommProject\Foundation\QueryManager\SimpleQueryManager`` is used. By default, parameters are passed as-is to the driver. It is somehow possible to explicitely declare the type of some or all the parameters in the query. The query manager will then use the `converter pooler`_ to convert them in a Postgresql format.

.. code:: php

    <?php
    //…
    use PommProject\Foundation\Converter\Type\Point;

    // Are there open bike stations around me ?
    $result = $session
        ->getQueryManager()
        ->query(
            "select station_id, public_name, available_slots
            from bike_station b
            where b.coordinates <@ circle($*::point, $*) and b.status = any($*::varchar[])",
            [new Point($position), $radius, ['full', 'reduced']]
        );

The example above shows how to pass simple but also complex parameters like geomtric types and arrays.

Adding custom poolers and clients
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Poolers and clients must implement ``ClientPoolerInterface`` and ``ClientInterface`` respectively. To make this process easier, it is somehow possible to extend the ``ClientPooler`` that uses the ``ClientPoolerTrait`` (or to use this trait directly). It will make custom class to work in a given way when a client is requested:

1.  If the client exists, it is fetched from the pool and returned (``getClient($identifier)`` and ``getClientFromPool($identifier)``).
2.  If the client does not exist, it is created, registered to the session and returned (``createClient($identifier)``)

The methods above can of course be overloaded. The only methods let to the developer are:

:``getPoolerType()``: That returns the client type handled by this pooler.
:``createClient($identifier)``: How to create a client of this type.


Result iterators
----------------

basic usage
~~~~~~~~~~~

There are two kinds of iterators that can be used with Pomm:

:``ResultIterator``:
    Implements all the methods for ``\SeekableIterator``, ``\Countable`` and ``\JsonSerializable`` interfaces. It just returns the raw results as they are fetched from the driver.

:``ConvertedResultIterator``:
    It extends ``ResultIterator`` but uses the converter pooler (see `Converter pooler`_) to convert data to a PHP representation. This is the one used by default by the query managers.

These iterators do fetch data lazily, this means rows are fetched on demand. This presents significant advantages in terms of performances and memory consumption. Furthermore, Pomm’s iterators are scrollable which means they are seek-able and they can be traversed several times.

.. code:: php

    <?php
    //…
    $results = $session
        ->getQueryManager()
        ->query("select generate_series(1, $*::int4) as a_number", [10])
        // ↑ generates from 1 to 10 (passed as parameter)
        ;

    $results->get(0); // returns ["a_number" => 1];
    $results->get(9); // returns ["a_number" => 10];

    try {
        $results->get(10);
    } catch (\OutOfBoundsException $e) {
        // index starts from 0
    }

    foreach ($results as $index => $result) { // traverse results
        printf("Result %02d => %d\n", $index, $result['a_number']);
    }

Expanding iterators
~~~~~~~~~~~~~~~~~~~

Even though iterators are lazy, it is possible to fetch all the results in one step and store them in memory.

:``extract()``:
    Simple dump an array of rows like ``PDO::fetchAll()``.

:``slice($column_name)``:
    return a one dimension array of the values stored in this result’s column.

Since the iterators implement the ``\JsonSerializable`` interface it is possible to simply export them in the JSON format by calling ``json_encode($iterator)``.

Other methods
~~~~~~~~~~~~~

Result iterators also propose handy methods 

:``current()``:
    Return the row pointed by the current cursor’s position in the result. This is used most of the time to extract a row in single result query like ``SELECT count(*) FROM …``.

:``count()``:
    Returns the number of rows of the result. Required by the ``\Countable`` interface.

:``isEmpty()``:
    Returns if the result set is empty (no results) or not.

:``isFirst``:
    If the result is not empty, it returns true if the iterator points on the first result. This is sometimes interesting if the iterator is traversed in the view (html templates or so) to add table informations prior to the first line.

:``isLast()``:
    If the result is not empty, it returns true if the iterator points on the last result. (see ``isFirst``).

:``isOdd()``:
    Returns true if the current cursor position is not divisible by two. Handy to easily change the background color of a result set a row on two.

:``isEven()``:
    Opposite of ``isOdd()``.

Where: the condition builder
----------------------------

Basic usage
~~~~~~~~~~~

Pomm comes with a dedicated class to build SQL conditions dynamically: the ``Where`` class. It use is pretty straightforward:

.. code:: php

    <?php
    use PommProject\Foundation\Where;
    //…
    $sql = "SELECT * FROM a_table WHERE :condition"
    $where = new Where();
    strtr($sql, [':condition' => $where]); // … WHERE true

    $where->andWhere('a is null');
    strtr($sql, [':condition' => $where]); // … WHERE a is null

    $where->andWhere('b');
    strtr($sql, [':condition' => $where]); // … WHERE a is null AND b

    $where->orWhere('not c');
    strtr($sql, [':condition' => $where]); // … WHERE (a is null AND b) OR not c

The example above shows how it deals with operator precedence. For convenience, it is possible to directly pass a ``Where`` class as argument to the ``andWhere`` and ``orWhere`` methods:

.. code:: php

    $where = new Where('a is not null');
    $where->orWhere(Where::create('b')->andWhere('not c'));
    // a is not null OR (b AND not c)

Dealing with parameters
~~~~~~~~~~~~~~~~~~~~~~~

Most of the time, condition clauses do rely on external parameters. The ``Where`` clause allows them to be attached to the condition they belong to so they can be passed in the right order to a ``query`` method:

.. code:: php

    $where = Where::create("status = $*", [$parameter1])
        ->andWhere("amount > $*", [$parameter2])
        ;

    $sql = strtr(
        "select … from a_table where :condition",
        [
            ':condition' => $where,
        ]
    );

    $results = $session
        ->getQueryManager()
        ->query($sql, $where->getValues())
        ;

There are special clauses to handle the SQL ``IN`` operator:

.. code:: php

    $where = Where::createWhereIn("status",
        [
            $parameter1,
            $parameter2,
            …,
            $parameterN,
        ]
    );
    // status IN ($*, $*, …, $*)

There is obviously a complementary ``createWhereNotIn`` method.

