=======================
Pomm-project Foundation
=======================

Overview
--------

Foundation is a light, fast and versatile PHP Postgresql database framework. It can either be used on its own (to replace a DBAL by example) or to build a more complex `model manager`_. It is the first stone of `Pomm project`_ version 2.

..  _`Pomm project`: http://www.pomm-project.org
..  _`model manager`: https://github.com/pomm-project/ModelManager

Foundation manages relations between a database *connection* and *clients* through *sessions*. It consists of several classes to configure, open, deal with and close *sessions*.

 - ``Pomm`` is the service class, it registers *session builders* and cache spawned *sessions*.
 - ``SessionBuilder`` configure and build *sessions*.
 - ``Session`` holds *clients* and *poolers* and the *connection*.
 - ``Client`` abstract class that implements ``ClientInterface``. Instances are *session*'s *clients*.
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

:note 1: This returns a *session* using a *session builder*.
:note 2: This returns the default ``query_manager`` *client* using the ``QueryManagerPooler``.
:note 3: Issue the parametrized query and return a ``ConvertedResultIterator``.
:note 4: Traverse the result as an array and fetch rows.
:note 5: Access field result. Those results are converted to a PHP equivalent type (see `Converter pooler`_).

Pomm service
------------

Pomm service is an interface to easily declare and build *sessions* through *session builders*.

Adding session builders
~~~~~~~~~~~~~~~~~~~~~~~

It is possible to declare session builders either using ``Pomm``'s class constructor or the ``addBuilder`` method:

.. code:: php

    <?php

    $pomm = new Pomm(['first_db' => ['dsn' => 'pgsql://user:pass@host/first_db']]);
    $pomm->addBuilder('second_db', new MySessionBuilder(['dsn' => 'pgsql://user:pass@host/second_db']));

It is often more practial to declare all *sessions* configuration from the constructor directly even if the builder is a custom class:

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
                    'class:session_builder' => '\Project\MySessionBuilder'
                ]
        ]
    );

Each session builder has a name. This name is important, it represents a configuration and is not coupled with the DSN. This is particularly useful when an application has to switch from a database to another with the same configuration.

Spanwing sessions
~~~~~~~~~~~~~~~~~

The easiest way to get a session from the *service* is to use the ``ArrayAccess`` implementation:

.. code:: php

    <?php

    $session = $pomm['first_db'];

    // this is strictly equivalent to

    $session = $pomm->getSession('first_db');

The ``getSession($name)`` method checks if a *session* using this *session builder* has already been created. If yes, it is returned, otherwise a new one is created using the ``createSession($name)``. This last method creates a new session every time it is called. This implies a new database connection will be used.

Session builder
---------------

*Session builders* are meant to configure and instantiate *sessions*. It is possible to use them on their own without ``Pomm`` *service*.

Configuration
~~~~~~~~~~~~~

There are several ways set configuration:

.. code:: php

    <?php

    $session_builder = new SessionBuilder(['dsn' => 'pgsql://user:pass@host:port/db_name']);
    $session_builder->addParameter('my_parameter', 'my_value');

In a more general way, ``SessionBuilder`` class is made to be overloaded by a project dedicated *session builder* class. It is then possible to overload the ``getDefaultConfiguration()`` method. It keeps the class configurable with a custom default configuration.

Session customization
~~~~~~~~~~~~~~~~~~~~~

The ``SessionBuilder`` class is made to be overloaded. Foundation package incidentally proposes two *session builders*:

 - ``PommProject\Foundation\Session\SessionBuilder`` blank session builder.
 - ``PommProject\Foundation\SessionBuilder`` builder with Foundation *clients* and *poolers* loaded and configured.

It is a encouraged to create a project dedicated *session builder* that overload one of these classes. Several methods are available to change a *session builder* behavior:

:``preConfigure()``:    Change the configuration just before a session is instantiated.
:``postConfigure($session)``:  Place where default *session poolers* and *clients* are registered into a brand new *session*.
:``createSession()``:  If a custom session class is to be instantiated.
:``createClientHolder()``:  If a custom *session holder* is to be used from within the *session*.
:``initializeConverterHolder()``:  Customize the *converter holder*. Remember all *sessions* created by the builder will have this converter holder whatever their DSN.

Converter holder
~~~~~~~~~~~~~~~~

The *converter holder* is a special configuration setting. It holds all the converters and is cloned when passed as parameter to the `converter pooler`_. A pre-configured customized *converter holder* can be passed as parameter to the *session builder*'s constructor:

.. code:: php

    <?php

    $session_builder = new SessionBuilder(
        ['dsn' => 'pgsql://user:pass@host:port/db_name'],
        new MyConverterHolder()
        );

The ``initializeConverterHolder()`` method is used internally to register default Postgresql types converters, use it to add your own default converters. The ``ConverterHolder`` instance is passed as reference. Remember this converter holder will be used for **all** sessions created by the builder whatever their DSN. If a database specific converter is to be registered, the best place might be the ``postConfigure`` method, dealing directly with the `converter pooler`_.

Session
-------

*Session* is the keystone of the Foundation package. It provides a *connection* API to *clients*. To be able to do this, *clients* must register to the *session* using the ``registerClient(ClientInterface)`` method. The *session* adds the *client* in the *client pool*. In exchange, it injects itself in the *client* using the ``initialize(Session)`` method (see `Client`_). Starting from this, the *client* can use the *connection* and other *clients*.

*Clients* are accessed using the ``getClient($type, $identifier)`` method. If no clients match the corresponding type and identifier, ``null`` is returned. This can be a problem when you expect a client to be present or to manage to instantiate one when needed. This is the role of the *client poolers* (aka *poolers*). *Poolers* are, in a way, *clients* manager for a given type. Not all types need a *pooler*, by example, the ``fixture`` clients type manage database test structures and data. They are here to create tables and types needed by tests on startup and to drop them on shutdown. Alternatively, the `prepared query pooler`_ takes the sql query as client identifier. If the given query has already been performed, it is re used. Otherwise, a new statement is prepared and then executed. When the *connection* goes down, all statements are deallocated.

Some *clients* may use *clients* from different types using their respective *poolers*. By example, the ``PreparedQueryManager`` *client* uses the `query manager pooler`_ and then the `converter pooler`_.

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

A *client pooler* manage *clients* of a given type. Its role is to return a client or throw an exception otherwise.

All *client poolers* must implement ``ClientPoolerInterface``. It is possible to easily implements this either by extending ``ClientPooler`` or using ``ClientPoolerTrait`` (the abstract class uses the trait). The interface defines three methods:

:``getPoolerType()``:   Return the type of *clients* managed by this *pooler*, not implemented in ``ClientPoolerTrait``.
:``register(Session)``:  When the *pooler* is registered to the session, the session injects itself in the *pooler* using this method.
:``getClient($identifier)``:    Method called to fetch a *client* using this *pooler*.

Because most *poolers* behave the same way, the ``ClientPoolerTrait`` add methods to work like the following. When a *client* is requested:

#. Retrieve the client from the *session*'s *client holder*.
#. If null is returned, it launches ``createClient($identifier)`` method.
#. If the *client* cannot be created, an exception must be thrown.
#. Return the *client*.

Default client poolers
----------------------

Here is a comprehensive list of the *poolers* registered by default with ``PommProject\Foundation\SessionBuilder``.

Converter pooler
~~~~~~~~~~~~~~~~

:Type:  converter

Responsible of proposing converter *clients*. If a client is not found, it checks in the *converter holder* if the given type has a converter. If yes, it wrap the *converter* in a ``ConverterClient`` and register it to the session. There are as many ``ConverterClient`` as registered types but they can share the same *converter* instances.

This way, it is possible to add custom converters or converters for database specific types like composite types. The best place to do that is in a `Session builder`_'s ``postConfigure(Session)`` method:

.. code:: php

    <?php
    //…
    function postConfigure(Session $session)
    {
        $session
            ->getPoolerForType('converter')
            ->getConverterHolder()
            ->addTypeToConverter('my_schema.latlong', 'Point') // ← convert a domain of point
            ->registerConverter('Hstore', new PgHstore(), ['hstore']) // ← register Hstore converter
            ;
    }

Even though the converters coming with Foundation cover a broad range of Postgresql's type, it is possible to write custom converters as soon as they implement ``ConverterInterface``. Be aware that the format of the data coming from Postgres may be configuration dependant (dates, money, number etc.). Default converters fit the default configuration set in the `Session builder`_.


Inspector pooler
~~~~~~~~~~~~~~~~

:Type:  inspector

This *pooler* calls the ``PommProject\Foundation\Inspector\Inspector`` *client* by default. It is possible to specify another *client* class as identifier, the *pooler* will try to instantiate it.

The inspector proposes methods to get information about database structure (schemas, tables, fields etc.).

Listener pooler
~~~~~~~~~~~~~~~

:Type:  listener

A ``Listener`` is a class that can hold anonymous functions that are triggered when the listener receive a notification with the listener's name.

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

Observer *pooler* aims at leveraging the ``LISTEN/NOTIFY`` mechanism in Postgresql. An observer *client* can be used to listen to Postgresql events sent with the ``NOTIFY`` SQL command. It is possible to ask the observer either to send back the event payload if any or to throw a ``NotificationException`` when a notification is caught.


Prepared query pooler
~~~~~~~~~~~~~~~~~~~~~

:Type: prepared_query

This *pooler* prepares statements if they do not already exist and execute them with parameters:

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

The query manager *pooler* returns a traversable iterator on converted results. The default *client* is a simple parametrized query but Foundation also comes with a prepared query manager:

.. code:: php

    <?php
    //…
    $result = $session
        ->getQueryManager('\PommProject\Foundation\PreparedQuery\PreparedQueryManager')
        ->query('select * from my_table where some_field = $*', ['some_content'])
        ;

If no client class is provided, the default ``PommProject\Foundation\QueryManager\SimpleQueryManager`` is used.


