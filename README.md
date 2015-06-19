# Foundation

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/pomm-project/Foundation/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/pomm-project/Foundation/?branch=master) [![Build Status](https://travis-ci.org/pomm-project/Foundation.svg?branch=master)](https://travis-ci.org/pomm-project/Foundation) [![Monthly Downloads](https://poser.pugx.org/pomm-project/foundation/d/monthly.png)](https://packagist.org/packages/pomm-project/foundation) [![License](https://poser.pugx.org/pomm-project/foundation/license.svg)](https://packagist.org/packages/pomm-project/foundation)

This is the foundation component for the Pomm database framework. It works only with PHP >= 5.4.4 and PostgreSQL >= 9.1.

Pomm Foundation is in Release Candidate (RC) state. This version will go in stable state soon and may be used for test purposes or to start new projects. 

## Installation

Pomm components are available on [packagist](https://packagist.org/packages/pomm-project/) using [composer](https://packagist.org/). To install and use Pomm's foundation, add a require line to `"pomm-project/foundation"` in your `composer.json` file.

## What is Foundation ?

It is the main block of Pomm database framework. It handles connection configuration and sessions. If you are looking for a library to use PostgreSQL in your web development, you might want to look at [Pomm's model manager](https://github.com/pomm-project/ModelManager). If you want to create a database access layer, Foundation is the right tool.

The easiest way to open a connection to the database server:

```php
<?php

/*
 ... autoloading stuff ..
*/

use PommProject\Foundation\Pomm;

// instantiate the service with the configuration as parameter:
$pomm = new Pomm(['my_db' => ['dsn' => 'pgsql://user:pass@host:port/db_name']]);

// get a session from the service:
$session = $pomm['my_db'];

// or get a default session from the service:
$session = $pomm->getDefaultSession();
```

If you have multiple session builders, you can specify a default one :

```php
// instantiate the service with the configuration as parameter:
$pomm = new Pomm([
    'my_db' => ['dsn' => 'pgsql://user:pass@host:port/db_name'],
    'my_db2' => 
        [
            'dsn'          => 'pgsql://user:pass@host:port/db_name2',
            'pomm:default' => true
        ]
    ]);

// get a default session (my_db2) from the service:
$session = $pomm->getDefaultSession();
```

## Sessions, clients and poolers

The `Session` instance is the keystone of Foundation. It is a client manager for the database connection handler. A client is a class that needs to interact with the database. It registers to the Session so the session injects into it. As soon as a client is registered, it gets access to the database connection and all other clients in the same time. Furthermore, the session does shutdown all the clients properly when going down which may be useful if clients rely on database structure (prepared queries, temporary tables etc.).

All model files are in a way clients of a session. By example, converter classes or prepared statements are clients of a session. To use a client from the session, call the `$session->getClient()` method. The problem here is that when no client is found, null is returned. To manage clients creation in the pool, `ClientPooler` can be registered. Most of the time, they check in the pool to see if the asked client is registered, if not they instantiate it, register it and send it back. It is possible to ask for a client through a `ClientPooler` using `$session->getClientUsingPooler()` method.

```php
<?php
// ...

$pomm = new Pomm(['my_db' => ['dsn' => 'pgsql://user:pass@host:port/db_name']]);
$pomm['my_db']
    ->getPoolerForType('prepared_query')
    ->getClient('select * from student where age > $*')
    ->execute([20])
    ;
```

For convenience, it is possible to request a client through a pooler using Session's `__call` method:

```php
<?php
// ...

$pomm = new Pomm(['my_db' => ['dsn' => 'pgsql://user:pass@host:port/db_name']]);
$pomm['my_db']
    ->getPreparedQuery('select * from student where age > $*')
    ->execute([20])
    ;
```

The point here is to understand that the instantiated clients are automatically reused when they are called several times. Clients are shutdown properly when the session is destroyed by PHP. The second strong point of this system is that all clients own a pointer to the session. So it can use other clients from it.

## Query manager and converter system.

With Foundation, performing a query and returning a converted result iterator is performed by the `QueryManager` client.

```php
<?php
//…
$pomm = new Pomm(['my_db' => ['dsn' => 'pgsql://user:pass@host:port/db_name']]);
$iterator = $pomm['my_db']
    ->getQueryManager()
    ->query('select * from student where age > $*', [20])
    ;

foreach($iterator as $student) {
    printf("Student id = '%d', age = '%d', name = '%s'.\n",
        $student['id'],
        $student['age'],
        $student['name']
        );
}
```

The `query` method returns an iterator on results. Data can then be fetched on demand from the database. All values are **converted** using Foundation converter system. It is also possible to fetch column-oriented results instead of row-oriented using the `slice()` method:

```php
<?php
//…
$pomm = new Pomm(['my_db' => ['dsn' => 'pgsql://user:pass@host:port/db_name']]);
$iterator = $pomm['my_db']
    ->getQueryManager()
    ->query('select * from student where age > $*', [20]);

printf("Names are: %s.\n", join(', ', $iterator->slice('name')));
```

It is possible to use your own Query class as long as it implements `QueryInterface`:

```php
$result = $pomm['my_db']
    ->getQueryManager('\PommProject\Foundation\PreparedQuery\PreparedQueryManager')
    ->query('select * from student where age > $*', [20])
    ;
```

The prepared query manager stores prepared statements and re-uses them when needed.

## Session builder

In order to create sessions, Pomm uses a session builder mechanism. By default, Foundation provides a full featured builder, but it is possible -- and advised -- to use a dedicated session builder class:

```php
$pomm = new Pomm([
    'my_db' =>
        [
            'dsn'                   => 'pgsql://user:pass@host:port/db_name',
            'class:session_builder' => '\My\Project\SessionBuilder',
        ]
    ]);
```

For convenience, there are two `SessionBuilder`s, one that just creates a blank session and the other that registers all poolers and clients needed for foundation to work:

 * `PommProject\Foundation\Session\SessionBuilder` vanilla session builder.
 * `PommProject\Foundation\SessionBuilder` full featured session builder.

## Tests

This package uses Atoum as unit test framework. The tests are located in `sources/tests`. The test suite needs to access the database to ensure that read and write operations are made in a consistent manner. You need to set up a database for that and fill the `sources/tests/config.php` file with the according DSN. For convenience, Foundation provides two classes that extend `Atoum` with a `Session`:

 * `PommProject\Foundation\Tester\VanillaSessionAtoum`
 * `PommProject\Foundation\Tester\FoundationSessionAtoum`

Making your test class to extend one of these will grant them with a `buildSession` method that returns a newly created session. Clients of these classes must implement a `initializeSession(Session $session)` method (even a blank one). It is often a good idea to provide a fixture class as a session client, this method is the right place to register it.
