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
use PommProject\Foundation\Where;

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
:condition
SQL;

        $where = Where::create('n.nspname =  $*', [$schema])
            ->andWhere('c.relname = $*', [$table])
            ;

        $iterator = $this->executeSql($sql, $where);

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
     * @return ResultIterator|null
     */
    public function getTableFieldInformation($oid)
    {
        $sql = <<<SQL
select
    att.attname      as "name",
    typ.typname      as "type",
    def.adsrc        as "default",
    att.attnotnull   as "is_notnull",
    dsc.description  as "comment",
    att.attnum       as "position",
    att.attnum = any(ind.indkey) as "is_primary"
from
  pg_catalog.pg_attribute att
    join pg_catalog.pg_type typ  on att.atttypid = typ.oid
    join pg_class           cla  on att.attrelid = cla.oid
    left join pg_description dsc      on cla.oid = dsc.objoid and att.attnum = dsc.objsubid
    left join pg_attrdef     def      on att.attrelid = def.adrelid and att.attnum = def.adnum
    left join pg_catalog.pg_index ind on cla.oid = ind.indrelid
where
:condition
order by
    att.attnum
SQL;
        $where = Where::create('att.attrelid = $*', [$oid])
            ->andWhere('att.attnum > 0')
            ->andWhere('not att.attisdropped')
            ;

        return $this->executeSql($sql, $where);
    }

    /**
     * getSchemaOid
     *
     * Return the given schema oid, null if the schema is not found.
     *
     * @access public
     * @param  string $name
     * @param  Where  $where optionnal where clause.
     * @return int|null
     */
    public function getSchemaOid($schema, Where $where = null)
    {
        $condition =
            Where::create("s.nspname = $*", [$schema])
            ->andWhere($where)
            ;
        $sql = <<<SQL
select
    s.oid as oid
from
    pg_catalog.pg_namespace s
where
    :condition
SQL;

        $iterator = $this->executeSql($sql, $condition);

        return $iterator->isEmpty() ? null : $iterator->current()['oid'];
    }

    /**
     * getPrimaryKey
     *
     * Get relation's primary key if any.
     *
     * @access public
     * @param  int $table_oid
     * @return array|null
     */
    public function getPrimaryKey($table_oid)
    {
        $sql = <<<SQL
select
    array_agg(att.attname) as fields
from
    pg_catalog.pg_attribute att
        join pg_catalog.pg_index ind on att.attrelid = ind.indexrelid
where
    :condition
SQL;
        $condition =
            Where::create('ind.indrelid = $*', [$table_oid])
            ->andWhere('ind.indisprimary')
            ;

        $pk = $this->executeSql($sql, $condition)->current();

        return $pk['fields'][0] === null ? [] : array_reverse($pk['fields']);
    }

    /**
     * getSchemaRelations
     *
     * Return informations on relations in a given schema. An additional Where
     * condition can be passed to filter against other criterias.
     *
     * @access public
     * @param  int    $schema_oid
     * @param  Where  $where
     * @return array|null
     */
    public function getSchemaRelations($schema_oid, Where $where = null)
    {
        $condition = Where::create('relnamespace = $*', [$schema_oid])
            ->andWhere(Where::createWhereIn('relkind', ['r', 'v']))
            ->andWhere($where)
            ;

        $sql = <<<SQL
select
    cl.relname      as "name",
    case
        when cl.relkind = 'r' then 'table'
        when cl.relkind = 'v' then 'view'
        else 'other'
    end             as "type",
    cl.oid          as "oid",
    des.description as "comment"
from
    pg_catalog.pg_class cl
        left join pg_catalog.pg_description des on
            cl.oid = des.objoid and des.objsubid = 0
where :condition
order by name asc
SQL;

        return $this->executeSql($sql, $condition);
    }

    /**
     * getTableComment
     *
     * Return the comment on a table if set. Null otherwise.
     *
     * @access public
     * @param  int    $table_oid
     * @return string|null
     */
    public function getTableComment($table_oid)
    {
        $sql      = <<<SQL
select description from pg_catalog.pg_description where :condition
SQL;

        $where    = Where::create('objoid = $*', [$table_oid]);
        $iterator = $this->executeSql($sql, $where);

        return $iterator->isEmpty() ? null : $iterator->current()['description'];
    }

    /**
     * executeSql
     *
     * Launch query execution.
     *
     * @access protected
     * @param  string $sql
     * @param  Where $condition
     * @return ResultIterator
     */
    protected function executeSql($sql, Where $condition = null)
    {
        $condition = (new Where)->andWhere($condition);
        $sql = strtr($sql, [':condition' => $condition]);

        return $this
            ->getSession()
            ->getQuery()
            ->query($sql, $condition->getValues())
            ;
    }
}
