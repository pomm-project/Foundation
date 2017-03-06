# Foundation

[![Latest Stable Version](https://poser.pugx.org/pomm-project/foundation/v/stable)](https://packagist.org/packages/pomm-project/foundation) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/pomm-project/Foundation/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/pomm-project/Foundation/?branch=master) [![Build Status](https://travis-ci.org/pomm-project/Foundation.svg?branch=master)](https://travis-ci.org/pomm-project/Foundation) [![Monthly Downloads](https://poser.pugx.org/pomm-project/foundation/d/monthly.png)](https://packagist.org/packages/pomm-project/foundation) [![License](https://poser.pugx.org/pomm-project/foundation/license.svg)](https://packagist.org/packages/pomm-project/foundation)

This is the **experimental branch** of the  foundation component of [Pomm Project](http://www.pomm-project.org).

## What is Foundation ?

Foundation is the main block of Pomm database framework. It makes clients able to communicate either with the database or with each others through a session. One of these clients -- the query manager -- can make Foundation to be used as DBAL replacement. If you are looking for a library to use PostgreSQL in your web development, you might want to look at [Pomm’s model manager](https://github.com/pomm-project/ModelManager). If you want to create a custom database access layer or just perform SQL queries, Foundation is the right tool.

Foundation provides out of the box:

 * Converters (all built-in Postgresql types are supported + arrays, HStore etc.) see [this SO comment](http://stackoverflow.com/questions/31643297/pg-query-result-contains-strings-instead-of-integer-numeric/31740990#31740990).
 * Prepared Queries.
 * Parametrized queries.
 * Seekable iterators on results.
 * LISTEN / NOTIFY asynchronous messages support.
 * Service manager for easy integration with dependency injection containers.

[See more with code examples on this blog post](http://www.pomm-project.org/news/a-short-focus-on-pomm-s-foundation.html).

## Requirements

 * PHP 5.4.4
    * mod-pgsql (not PDO)
 * Postgresql 9.1

## Installation

Pomm components are available on [packagist](https://packagist.org/packages/pomm-project/) using [composer](https://packagist.org/). To install and use Pomm's foundation, add a require line to `"pomm-project/foundation"` in your `composer.json` file.

## Documentation

Foundation’s documentation is available [either online](https://github.com/pomm-project/Foundation/blob/master/documentation/foundation.rst) or directly in the `documentation` folder of the project.

## Tests

This package uses [Atoum](https://github.com/atoum/atoum) as unit test framework. The tests are located in `sources/tests`. The test suite needs to access the database to ensure that read and write operations are made in a consistent manner. You need to set up a database for that and fill the `sources/tests/config.php` file with the according DSN. For convenience, Foundation provides two classes that extend `Atoum` with a `Session`:

 * `PommProject\Foundation\Tester\VanillaSessionAtoum`
 * `PommProject\Foundation\Tester\FoundationSessionAtoum`

Making your test class to extend one of these will grant them with a `buildSession` method that returns a newly created session. Clients of these classes must implement a `initializeSession(Session $session)` method (even a blank one). It is often a good idea to provide a fixture class as a session client, this method is the right place to register it.

## Known bugs

Unfortunately there is a bug we can not fix easily or without degrading performances of the whole stack:
 * The `ConvertedResultIterator` can not recognize custom composite types when they are defined in schemas other than `public`. This is because the `pg_type` function does not return the schema the type belongs to. There are not turns around unless the schema is inspected manually by issuing a lot of queries. (see #53)
