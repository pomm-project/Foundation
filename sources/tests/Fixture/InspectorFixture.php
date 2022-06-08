<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Fixture;

use PommProject\Foundation\Client\Client;
use PommProject\Foundation\Exception\ConnectionException;
use PommProject\Foundation\Exception\FoundationException;
use PommProject\Foundation\Exception\SqlException;
use PommProject\Foundation\Session\ResultHandler;
use PommProject\Foundation\Session\Session;

class InspectorFixture extends Client
{
    /**
     * @throws SqlException|FoundationException|ConnectionException
     */
    protected function executeAnonymousQuery(string $sql): ResultHandler|array
    {
        return $this
            ->getSession()
            ->getConnection()
            ->executeAnonymousQuery($sql);
    }

    public function getClientType(): string
    {
        return 'fixture';
    }

    public function getClientIdentifier(): string
    {
        return 'inspector';
    }

    /**
     * @throws SqlException|FoundationException|ConnectionException
     */
    public function createSchema(): void
    {
        $this->dropSchema();
        $sql = [
            "begin",
            "create schema inspector_test",
            "create type inspector_test.someone as (first_names varchar[], last_name varchar, age int)",
            "create type inspector_test.sponsor_rating as enum ('platinum', 'gold', 'silver', 'bronze', 'aluminium')",
            "create table inspector_test.no_pk (a_boolean bool, varchar_array character varying[])",
            "create table inspector_test.with_simple_pk (with_simple_pk_id int4 primary key, a_patron inspector_test.someone[], some_timestamps timestamptz[])",
            "create table inspector_test.with_complex_pk (with_complex_pk_id int4, another_id int4, created_at timestamp not null default now(), primary key (with_complex_pk_id, another_id))",
            "create index inspector_test_with_complex_pk_created_at on inspector_test.with_complex_pk (created_at)",
            "comment on table inspector_test.no_pk is 'This table has no primary key'",
            "comment on column inspector_test.with_complex_pk.with_complex_pk_id is 'Test comment'",
            "commit",
        ];
        $this->executeAnonymousQuery(join('; ', $sql));
    }

    /**
     * @throws SqlException|FoundationException|ConnectionException
     */
    public function renamePks($table, $old_pk, $new_pk)
    {
        $sql = sprintf(
            'alter table inspector_test."%s" rename "%s" to "%s"',
            $table,
            $old_pk,
            $new_pk
        );

        $this->executeAnonymousQuery($sql);
    }

    /**
     * @throws SqlException|FoundationException|ConnectionException
     */
    public function dropSchema()
    {
        $sql = "drop schema if exists inspector_test cascade";
        $this->executeAnonymousQuery($sql);
    }
}
