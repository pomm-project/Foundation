# Foundation

This is the foundation component for the Pomm database framework. It works only with PHP >= 5.4.4.

This is experimental software and it may be broken or not non functional. If you are looking for a stable library, look at [Pomm 1.x](http://www.pomm-project.org).

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/pomm-project/Foundation/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/pomm-project/Foundation/?branch=master)

## What is that ?

`Foundation` is the main block of Pomm database framework. It handles connection configuration and sessions.

The easiest way to open a connection to the database server:

```php
<?php

/*
 ... autoloading stuff ..
*/

// instantiate the service with the configuration as parameter:
$pomm = new \PommProject\Foundation\Pomm(['my_db' => ['dsn' => 'pgsql://greg/greg']]);

// get a session from the service:
$session = $pomm['my_db'];
```

## Sessions, clients and poolers

The `Session` instance is the keystone of Foundation. It is a client manager for the database connection handler. A client is a class that needs to interact with the database. All model files are in a way clients of a session. By example, prepared statements are clients of a session. To be able to do its job it needs to do the following:

 * Check if a prepared query already exist
 * If not, instantiate a PreparedQuery and register as client to the session.
 * Execute the query.

This is a bit complicated, in order to make developers life a bit more easy, interactions between clients and session may be delegated to dedicated poolers. All the following steps then become:

```php
<?php
// ...

$pomm = new \PommProject\Foundation\Pomm(['my_db' => ['dsn' => 'pgsql://greg/greg']]);
$pomm['my_db']
    ->getPoolerForType('prepared_query')
    ->getClient('select * from student where age > $*')
    ->execute([20])
    ;
```

This can also be written as the following:

```php
<?php
// ...

$pomm = new \PommProject\Foundation\Pomm(['my_db' => ['dsn' => 'pgsql://greg/greg']]);
$pomm['my_db']
    ->getPreparedQuery('select * from student where age > $*')
    ->execute([20])
    ;
```

The point here is to understand that the instantiated clients are automatically reused when they are called several times. Clients are shutdown properly when the session is destroyed by PHP. The second strong point of this system is that all clients own a pointer to the session. So it can use other clients from it.

## How to develop with Foundation ?

In most cases you are interested in using structures and entities from a database schema. This is the job of the Model Manager client provided with another package (yet to create). There may be cases where Model Manager would not fit the job, especially in case of legacy applications (among others):

 * Access to the database are stored procedures only.
 * SQL queries are stored in separate files in different directories.
 * You want stats, not entity related computations.
 * …

It is easy to develop client classes and poolers to interact with the session, it is just a matter of implementing the `ClientInterface` or `ClientPoolerInterface` and register them in the session.

## Querying and converter system.

In foundation, performing a query and return a result as an iterator is a Client too:

```php
<?php
//…
$pomm = new \PommProject\Foundation\Pomm(['my_db' => ['dsn' => 'pgsql://greg/greg']]);
$iterator = $pomm['my_db']
    ->getQuery()
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

The `query` method returns an iterator on results. Data can then be fetched on demand from the database. All values are **converted** using Foundation converter system. It is also possible to fetch column oriented results instead of row oriented using the `slice()` method:

```php
<?php
//…
$pomm = new \PommProject\Foundation\Pomm(['my_db' => ['dsn' => 'pgsql://greg/greg']]);
$iterator = $pomm['my_db']
    ->query('select * from student where age > $*', [20]);

printf("Names are: %s.\n", join(', ', $iterator->slice('name')));
```

It is possible to use your own Query class as soon as it implements `QueryInterface`:

```php
$result = $pomm['my_db']
    ->getQuery(`My\Query\Class')
    ->query('select * from student where age > $*', [20])
    ;
```

## Tests and project structure

This package uses Atoum as unit test framework. The tests are located in `sources/tests`. The test suite needs to access the database to ensure read and write operations are made in a consistent manner. You need to set up a database for that and fill the `sources/tests/config.php` file with the according DSN.

```
sources/
├── lib
│   ├── Connection.php
│   ├── Inflector.php
│   ├── ParameterHolder.php
│   ├── Pomm.php
│   ├── QueryParameterExpander.php
│   ├── ResultIterator.php
│   ├── Session.php
│   ├── Where.php
│   ├── Client
│   │   ├── ClientHolder.php
│   │   ├── ClientInterface.php
│   │   ├── Client.php
│   │   ├── ClientPoolerInterface.php
│   │   └── ClientPooler.php
│   ├── Converter
│   │   ├── ConverterHolder.php
│   │   ├── ConverterInterface.php
│   │   └── …
│   ├── DatabaseConfiguration.php
│   ├── Exception
│   │   ├── ConnectionException.php
│   │   ├── ConverterException.php
│   │   ├── FoundationException.php
│   │   └── SqlException.php
│   ├── PreparedQuery
│   │   ├── PreparedQuery.php
│   │   └── PreparedQueryPooler.php
│   └── QueryManager
│       ├── QueryManagerInterface.php
│       └── SimpleQueryManager.php
└── tests
    ├── config.php
    ├── config.php.dist
    └── Unit
        │
        …

11 directories, 40 files
```
