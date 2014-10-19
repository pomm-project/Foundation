<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Fixture;

use PommProject\Foundation\Client\Client;
use PommProject\Foundation\Session\Session;

Class InspectorFixture extends Client
{
    protected function executeAnonymousQuery($sql)
    {
        return $this
            ->getSession()
            ->getConnection()
            ->executeAnonymousQuery($sql);
    }

    public function getClientType()
    {
        return 'fixture';
    }

    public function getClientIdentifier()
    {
        return 'inspector';
    }

    public function createSchema()
    {
        $this->dropSchema();
        $sql = [
            "begin",
            "create schema inspector_test",
            "create table inspector_test.no_pk (a_boolean bool, varchar_array character varying[])",
            "create table inspector_test.with_simple_pk (with_simple_pk_id int4 primary key, a_char char, some_timestamps timestamptz[])",
            "create table inspector_test.with_complex_pk (with_complex_pk_id int4, another_id int4, created_at timestamp not null default now(), primary key (with_complex_pk_id, another_id))",
            "create index inspector_test_with_complex_pk_created_at on inspector_test.with_complex_pk (created_at)",
            "comment on table inspector_test.no_pk is 'This table has no primary key'",
            "comment on column inspector_test.with_complex_pk.with_complex_pk_id is 'Test comment'",
            "create type inspector_test.someone as (first_names varchar[], last_name varchar, age int)",
            "create type inspector_test.sponsor_rating as enum ('platinum', 'gold', 'silver', 'bronze', 'aluminium')",
            "commit",
        ];
        $this->executeAnonymousQuery(join('; ', $sql));
    }

    public function dropSchema()
    {
        $sql = "drop schema if exists inspector_test cascade";
        $this->executeAnonymousQuery($sql);
    }
}
