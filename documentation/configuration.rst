=======================
Pomm-project Foundation
=======================

Overview
--------

Foundation is a light, fast and versatile PHP Postgresql database framework. It can either be used on its own (to replace a DBAL by example) or to build a more complex model manager. It is the first stone of `Pomm project`_ version 2.

..  _`Pomm project`: http://www.pomm-project.org

Foundation manages relations between a database *connection* and *clients* through *sessions*. It consists of several classes to configure, open, deal with and close *sessions*.

 - ``Pomm`` is the service class, it registers *session builders* and cache spawned *sessions*.
 - ``SessionBuilder`` configure and build *sessions*.
 - ``Session`` holds *clients* and *poolers* and the *connection*.
 - ``Client`` abstract class that implements ``ClientInterface``. Instances are *session*'s *clients*.
 - ``ClientPooler`` abstract class that implements ``ClientPoolerInterface``. They manage *clients* in *sessions*.

This complexity is at first glance hidden. If one wants to open a connection, send a query and get converted results, it is as simple as::

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
:note 5: Access field result. Those results are converted to a PHP equivalent type (see `Converter *client pooler*`_).

Pomm service
------------

Pomm service is an interface to easily declare and build *sessions* through *session builders*.

Adding session builders
~~~~~~~~~~~~~~~~~~~~~~~

It is possible to declare session builders either using ``Pomm``'s class constructor or the ``addBuilder`` method::

    <?php

    $pomm = new Pomm(['first_db' => ['dsn' => 'pgsql://user:pass@host/first_db']]);
    $pomm->addBuilder('second_db', new MySessionBuilder(['dsn' => 'pgsql://user:pass@host/second_db']));

It is often more practial to declare all *sessions* configuration from the constructor directly even if the builder is a custom class::

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

The easiest way to get a session from the *service* is to use the ``ArrayAccess`` implementation::

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

There are several ways set configuration::

    <?php

    $session_builder = new SessionBuilder(['dsn' => 'pgsql://user:pass@host:port/db_name']);
    $session_builder->addParameter('my_parameter', 'my_value');

In a more general way, ``SessionBuilder`` class is made to be overloaded by a project dedicated *session builder* class. It is then possible to overload the ``getDefaultConfiguration()`` method. It keeps the class configurable with a custom default configuration.

Session customization
~~~~~~~~~~~~~~~~~~~~~

The ``SessionBuilder`` class is made to be overloaded. ``Foundation`` package incidentally proposes two *session builders*:

 - ``PommProject\Foundation\Session\SessionBuilder`` blank session builder.
 - ``PommProject\Foundation\SessionBuilder`` builder with ``Foundation`` *clients* and *poolers* loaded and configured.

It is a encouraged to create a project dedicated *session builder* that overload one of these classes. Several methods are available to change a *session builder* behavior:

:``preConfigure()``:    Change the configuration just before a session is instantiated.
:``postConfigure($session)``:  Place where default *session poolers* and *clients* are registered into a brand new *session*.
:``createSession()``:  If a custom session class is to be instantiated.
:``createClientHolder()``:  If a custom *session holder* is to be used from within the *session*.
:``initializeConverterHolder()``:  Customize the *converter holder*. Remember all *sessions* created by the builder will have this converter holder whatever their DSN.

Converter holder
~~~~~~~~~~~~~~~~

The *converter holder* is a special configuration setting. It holds all the converters and is cloned when passed as parameter to the `converter *client pooler*`_. A pre-configured customized *converter holder* can be passed as parameter to the *session builder*'s constructor::

    <?php

    $session_builder = new SessionBuilder(
        ['dsn' => 'pgsql://user:pass@host:port/db_name'],
        new MyConverterHolder()
        );

The ``initializeConverterHolder()`` method is used internally to register default Postgresql types converters, use it to add your own default converters. The ``ConverterHolder`` instance is passed as reference. Remember this converter holder will be used for **all** sessions created by the builder whatever their DSN. If a database specific converter is to be registered, the best place might be the ``postConfigure`` method, dealing directly with the `converter *client pooler*`_.

Session
-------
