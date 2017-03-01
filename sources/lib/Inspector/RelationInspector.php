<?php
/*
 * This file is part of the Pomm package.
 *
 * (c) 2014 - 2017 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Inspector;

use PommProject\Foundation\Exception\FoundationException;
use PommProject\Foundation\ConvertedResultIterator;
use PommProject\Foundation\Client\Client;
use PommProject\Foundation\Where;

/**
 * RelationInspector
 *
 * Relation inspector.
 *
 * @package     Pomm
 * @copyright   2017 Grégoire HUBERT
 * @author      Grégoire HUBERT
 * @license     X11 {@link http://opensource.org/licenses/mit-license.php}
 *
 * @see Client
 */
class RelationInspector extends Client
{
    use InspectorTrait;

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
        return 'relation';
    }

    /**
     * getRelations
     *
     * Return a list of relations. Be aware that if no conditions is given, it
     * will also return system tables and views.
     *
     * @param   Where $where
     * @return  ConvertedResultIterator
     */
    public function getRelations(Where $where = null)
    {
        $condition = Where::createWhereIn('relkind', ['r', 'v', 'm', 'f'])
            ->andWhere($where)
            ;

        $sql = <<<SQL
select
    cl.relname      as "name",
    case
        when cl.relkind = 'r' then 'table'
        when cl.relkind = 'v' then 'view'
        when cl.relkind = 'm' then 'materialized view'
        when cl.relkind = 'f' then 'foreign table'
        else 'other'
    end             as "type",
    n.nspname       as "schema",
    cl.oid          as "oid",
    o.rolname       as "owner",
    case
        when cl.relkind = 'r' then pg_size_pretty(pg_relation_size(cl.oid::regclass))
        else null
    end             as "size",
    des.description as "comment"
from
    pg_catalog.pg_class cl
        left join pg_catalog.pg_description des on
            cl.oid = des.objoid and des.objsubid = 0
        join pg_catalog.pg_roles o on cl.relowner = o.oid
        join pg_catalog.pg_namespace n on cl.relnamespace = n.oid
where {condition}
order by name asc
SQL;

        return $this->executeSql($sql, $condition);
    }

    /**
     * getRelationsInSchema
     *
     * Return the list of relations contained in the given schema.
     *
     * @param   string $schema_name
     * @param   Where $where
     * @return  ConvertedResultIterator
     */
    public function getRelationsInSchema($schema_name, Where $where = null)
    {
        $where = Where::create("n.nspname ~* $*", [$schema_name])
            ->andWhere($where)
            ;

        return $this->getRelations($where);
    }

    /**
     * getDatabaseRelations
     *
     * Return non system relations in the database.
     *
     * @param   Where $where
     * @return  ConvertedResultIterator
     */
    public function getDatabaseRelations(Where $where = null)
    {
        $where = Where::create('n.nspname !~* $*', ['^pg_'])
            ->andWhere('n.nspname != $*', ['information_schema'])
            ->andWhere($where)
            ;

        return $this->getRelations($where);
    }

    /**
     * getTableFieldInformationWhere
     *
     * Get table's field information. If no fields are found, null is
     * returned.
     *
     * @param  Where $where
     * @return ConvertedResultIterator
     */
    protected function getTableFieldInformationWhere(Where $where)
    {
        $sql = <<<SQL
select
    att.attname      as "name",
    case
        when name.nspname = 'pg_catalog' then typ.typname
        else format('%s.%s', name.nspname, typ.typname)
    end as "type",
    def.adsrc        as "default",
    att.attnotnull   as "is_notnull",
    dsc.description  as "comment",
    att.attnum       as "position",
    att.attnum = any(ind.indkey) as "is_primary"
from
  pg_catalog.pg_attribute att
    join pg_catalog.pg_type  typ  on att.atttypid = typ.oid
    join pg_catalog.pg_class cla  on att.attrelid = cla.oid
    join pg_catalog.pg_namespace clns on cla.relnamespace = clns.oid
    left join pg_catalog.pg_description dsc on cla.oid = dsc.objoid and att.attnum = dsc.objsubid
    left join pg_catalog.pg_attrdef def     on att.attrelid = def.adrelid and att.attnum = def.adnum
    left join pg_catalog.pg_index ind       on cla.oid = ind.indrelid and ind.indisprimary
    left join pg_catalog.pg_namespace name  on typ.typnamespace = name.oid
where
{condition}
order by
    att.attnum
SQL;

        $where = $where
            ->andWhere('att.attnum > 0')
            ->andWhere('not att.attisdropped')
            ;

        return $this->executeSql($sql, $where);
    }

    /**
     * getTableFieldInformation
     *
     * Return table fields information given the table oid.
     *
     * @param   int $oid
     * @return  ConvertedResultIterator
     */
    public function getTableFieldInformation($oid)
    {
        return $this
            ->getTableFieldInformationWhere(
                Where::create('att.attrelid = $*', [$oid])
            );
    }

    /**
     * getTableFieldInformationName
     *
     * A short description here
     *
     * @param   string $schema
     * @param   string $name
     * @return  ConvertedResultIterator
     */
    public function getTableFieldInformationName($schema, $name)
    {
        return $this
            ->getTableFieldInformationWhere(
                Where::create("cla.relname = $*", [$name])
                    ->andWhere("clns.nspname = $*", [$schema])
            );
    }
}
