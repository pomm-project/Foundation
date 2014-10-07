<?php
/*
 * This file is part of the Pomm package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Inspector;

use PommProject\Foundation\Client\Client;

/**
 * Inspector
 *
 * Database structure inspector.
 *
 * @package Foundation
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see Client
 */
class Inspector extends Client
{
    /**
     * getClientType
     *
     * @see ClientInterface
     */
    public function getClientType()
    {
        return 'inspector';
    }

    /**
     * getClientIdentifier
     *
     * @see ClientInterface
     */
    public function getClientIdentifier()
    {
        return get_class($this);
    }

    /**
     * getTableOid
     *
     * Return the table oid from postgresql catalog. If no table is found, null
     * is returned.
     *
     * @access public
     * @param  string $schema
     * @param  string $table
     * @return int|null
     */
    public function getTableOid($schema, $table)
    {
        $sql = <<<SQL
select
  c.oid as oid
from
    pg_catalog.pg_class c
        left join pg_catalog.pg_namespace n on n.oid = c.relnamespace
where
        n.nspname =  $* and c.relname = $*
SQL;

        $iterator = $this
            ->getSession()
            ->getQuery()
            ->query($sql, [$schema, $table])
            ;

        return $iterator->isEmpty() ? null : $iterator->current()['oid'];
    }

    /**
     * getTableFieldInformation
     *
     * Get table's field informations. If no fields are found, null is
     * returned.
     *
     * @access public
     * @param  int    $oid
     * @return array|null
     */
    public function getTableFieldInformation($oid)
    {
        $sql = <<<SQL
select
    a.attname as attname,
    t.typname as type,
    n.nspname as type_namespace,
    (
        select
            substring(pg_catalog.pg_get_expr(d.adbin, d.adrelid) for 128)
        from
            pg_catalog.pg_attrdef d
        where
            d.adrelid = a.attrelid
            and
            d.adnum = a.attnum
            and
            a.atthasdef
  )            as defaultval,
  a.attnotnull as notnull,
  a.attnum     as index
from
  pg_catalog.pg_attribute a
    join pg_catalog.pg_type t on a.atttypid = t.oid
    join pg_namespace n on t.typnamespace = n.oid
where
    a.attrelid = $*
    and
    a.attnum > 0
    and
    not a.attisdropped
order by
    a.attnum
SQL;
        return $this
            ->getSession()
            ->getQuery()
            ->query($sql, [$oid])
            ->current()
            ;
    }

    /**
     * getSchemaOid
     *
     * Return the given schema oid, null if the schema is not found.
     *
     * @access public
     * @param  string $name
     * @return int|null
     */
    public function getSchemaOid($schema)
    {
        $sql = <<<SQL
select
    s.oid as oid
from
    pg_catalog.pg_namespace s on s.nspname = $*
SQL;

        $iterator = $this
            ->getSession()
            ->getQuery()
            ->query($sql, [$schema])
            ;

        return $iterator->isEmpty() ? null : $iterator->current()['oid'];
    }

    /**
     * getPrimaryKey
     *
     *TODO  Get relation's primary key if any.
     *
     * @access public
     * @param  int $table_oid
     * @return array|null
     */
    public function getPrimaryKey($table_oid)
    {
        $sql = <<<SQL
select
    regexp_matches(pg_catalog.pg_get_indexdef(i.indexrelid, 0, true), e'\\\\((.*)\\\\)', 'gi') as pkey i
from
    pg_catalog.pg_class c,
    pg_catalog.pg_class c2,
    pg_catalog.pg_index i
where
    c.oid = $*
    and
    c.oid = i.indrelid
    and
    i.indexrelid = c2.oid
order by
    i.indisprimary desc,
    i.indisunique desc,
    c2.relname
SQL;
    }

    /**
     * getSchemaRelations
     *
     * Return informations on relations in a given schema.
     *
     * @access public
     * @param  int    $schema_oid
     * @return array|null
     */
    public function getSchemaRelations($schema_oid)
    {
    }
}
